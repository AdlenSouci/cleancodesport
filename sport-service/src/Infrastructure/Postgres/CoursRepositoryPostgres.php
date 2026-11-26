<?php
namespace App\Infrastructure\Postgres;

use App\Application\ConflitDeVersion;
use App\Application\CoursRepository;
use App\Domain\Capacite;
use App\Domain\Cours;
use PDO;

final class CoursRepositoryPostgres implements CoursRepository
{
    private PDO $pdo;

    /** @var array<string, int> version connue au moment du chargement / dernier enregistrement */
    private array $versions = [];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function enregistrer(Cours $cours): void
    {
        $this->pdo->beginTransaction();
        try {
            $id = $cours->id();
            $existe = $this->existe($id);

            if (!$existe) {
                $stmt = $this->pdo->prepare(
                    'INSERT INTO cours (id, intitule, places, debut, version)
                     VALUES (:id, :intitule, :places, :debut, 1)'
                );
                $stmt->execute([
                    'id' => $id,
                    'intitule' => $cours->intitule(),
                    'places' => $cours->places(),
                    'debut' => $cours->debut()->format(\DateTimeInterface::ATOM),
                ]);
                $this->versions[$id] = 1;
            } else {
                $version = $this->versions[$id] ?? $this->lireVersion($id);
                $stmt = $this->pdo->prepare(
                    'UPDATE cours
                     SET intitule = :intitule,
                         places = :places,
                         debut = :debut,
                         version = version + 1
                     WHERE id = :id AND version = :version'
                );
                $stmt->execute([
                    'id' => $id,
                    'intitule' => $cours->intitule(),
                    'places' => $cours->places(),
                    'debut' => $cours->debut()->format(\DateTimeInterface::ATOM),
                    'version' => $version,
                ]);
                if ($stmt->rowCount() === 0) {
                    throw new ConflitDeVersion($id);
                }
                $this->versions[$id] = $version + 1;
            }

            $this->remplacerInscriptions($cours);
            $this->pdo->commit();
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    public function parIdentifiant(string $id): ?Cours
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, intitule, places, debut, version FROM cours WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
        $ligne = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($ligne === false) {
            return null;
        }

        return $this->depuisLigne($ligne);
    }

    /** @return list<Cours> */
    public function paginer(int $limit, int $offset): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, intitule, places, debut, version
             FROM cours
             ORDER BY debut ASC
             LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $cours = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $ligne) {
            $cours[] = $this->depuisLigne($ligne);
        }
        return $cours;
    }

    public function compter(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM cours')->fetchColumn();
    }

    private function existe(string $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM cours WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    private function lireVersion(string $id): int
    {
        $stmt = $this->pdo->prepare('SELECT version FROM cours WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $version = $stmt->fetchColumn();
        if ($version === false) {
            throw new ConflitDeVersion($id);
        }
        return (int) $version;
    }

    /** @param array<string, mixed> $ligne */
    private function depuisLigne(array $ligne): Cours
    {
        $id = (string) $ligne['id'];
        $this->versions[$id] = (int) $ligne['version'];

        [$inscrits, $attente] = $this->chargerInscriptions($id);

        return Cours::reconstituer(
            $id,
            (string) $ligne['intitule'],
            new Capacite((int) $ligne['places']),
            new \DateTimeImmutable((string) $ligne['debut']),
            $inscrits,
            $attente,
        );
    }

    /** @return array{0: list<string>, 1: list<string>} */
    private function chargerInscriptions(string $coursId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT participant_id, statut, position
             FROM inscriptions
             WHERE cours_id = :cours_id
             ORDER BY statut, position'
        );
        $stmt->execute(['cours_id' => $coursId]);

        $inscrits = [];
        $attente = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $participantId = (string) $row['participant_id'];
            if ($row['statut'] === 'inscrit') {
                $inscrits[] = $participantId;
            } else {
                $attente[] = $participantId;
            }
        }
        return [$inscrits, $attente];
    }

    private function remplacerInscriptions(Cours $cours): void
    {
        $delete = $this->pdo->prepare('DELETE FROM inscriptions WHERE cours_id = :cours_id');
        $delete->execute(['cours_id' => $cours->id()]);

        $insert = $this->pdo->prepare(
            'INSERT INTO inscriptions (cours_id, participant_id, statut, position)
             VALUES (:cours_id, :participant_id, :statut, :position)'
        );

        $position = 1;
        foreach ($cours->inscrits() as $participantId) {
            $insert->execute([
                'cours_id' => $cours->id(),
                'participant_id' => $participantId,
                'statut' => 'inscrit',
                'position' => $position++,
            ]);
        }

        $position = 1;
        foreach ($cours->listeAttente() as $participantId) {
            $insert->execute([
                'cours_id' => $cours->id(),
                'participant_id' => $participantId,
                'statut' => 'en_liste_attente',
                'position' => $position++,
            ]);
        }
    }
}
