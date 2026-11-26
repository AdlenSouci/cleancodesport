<?php
namespace Tests\Integration;

use App\Application\ConflitDeVersion;
use App\Domain\Capacite;
use App\Domain\Cours;
use App\Infrastructure\Postgres\CoursRepositoryPostgres;
use PDO;
use PHPUnit\Framework\TestCase;

final class CoursRepositoryPostgresTest extends TestCase
{
    private PDO $pdo;

    // UUID fixe (pas "1" : la colonne id est de type UUID en base)
    private const ID = '11111111-1111-4111-8111-111111111111';

    protected function setUp(): void
    {
        try {
            $this->pdo = new PDO(
                'pgsql:host=localhost;port=5433;dbname=sport',
                'app',
                'app',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (\Throwable $e) {
            $this->markTestSkipped('PostgreSQL indisponible : ' . $e->getMessage());
        }

        $this->pdo->exec('DELETE FROM inscriptions');
        $this->pdo->exec('DELETE FROM cours');
    }

    public function test_enregistrer_puis_relire(): void
    {
        $repo = new CoursRepositoryPostgres($this->pdo);

        $cours = new Cours(
            self::ID,
            'Cours de tennis',
            new Capacite(2),
            new \DateTimeImmutable('2026-12-01 10:00:00')
        );
        $cours->inscrire('22222222-2222-4222-8222-222222222222');

        $repo->enregistrer($cours);
        $lu = $repo->parIdentifiant(self::ID);

        $this->assertNotNull($lu);
        $this->assertSame(self::ID, $lu->id());
        $this->assertSame('Cours de tennis', $lu->intitule());
        $this->assertSame(2, $lu->places());
        $this->assertTrue($lu->estInscrit('22222222-2222-4222-8222-222222222222'));
    }

    public function test_ecriture_concurrente_est_refusee(): void
    {
        // 1) on crée le cours une première fois
        $repoInit = new CoursRepositoryPostgres($this->pdo);
        $repoInit->enregistrer(new Cours(
            self::ID,
            'Yoga',
            new Capacite(10),
            new \DateTimeImmutable('2026-12-01 10:00:00')
        ));

        // 2) deux repos chargent le même cours (même version en mémoire)
        $repoA = new CoursRepositoryPostgres($this->pdo);
        $repoB = new CoursRepositoryPostgres($this->pdo);
        $a = $repoA->parIdentifiant(self::ID);
        $b = $repoB->parIdentifiant(self::ID);
        $this->assertNotNull($a);
        $this->assertNotNull($b);

        // 3) A enregistre OK → version passe à 2 en base
        $repoA->enregistrer($a);

        // 4) B a encore l'ancienne version → doit échouer
        $this->expectException(ConflitDeVersion::class);
        $repoB->enregistrer($b);
    }
}
