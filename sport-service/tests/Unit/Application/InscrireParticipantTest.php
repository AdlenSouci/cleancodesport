<?php
namespace Tests\Unit\Application;

use App\Application\CoursIntrouvable;
use App\Application\CreerCours;
use App\Application\InscrireParticipant;
use App\Domain\ResultatInscription;
use App\Infrastructure\Memoire\CoursRepositoryEnMemoire;
use PHPUnit\Framework\TestCase;
use Tests\Doublures\GenerateurIdentifiantPrevisible;

final class InscrireParticipantTest extends TestCase
{
    private CoursRepositoryEnMemoire $repository;
    private InscrireParticipant $inscrireParticipant;

    // Exécuté avant CHAQUE test : chaque test repart d'un repository vide,
    // donc aucun test ne dépend de l'ordre d'exécution (critère du TP).
    protected function setUp(): void
    {
        $this->repository = new CoursRepositoryEnMemoire();
        $this->inscrireParticipant = new InscrireParticipant($this->repository);
    }

    // Prépare un cours existant via le cas d'usage 1 ; renvoie son id ("cours-1").
    private function unCoursExistant(int $places): string
    {
        $creerCours = new CreerCours($this->repository, new GenerateurIdentifiantPrevisible());

        return $creerCours->executer('Yoga', $places, new \DateTimeImmutable('2026-12-01 10:00:00'))->id();
    }

    public function test_un_participant_est_inscrit_et_l_inscription_est_enregistree(): void
    {
        $coursId = $this->unCoursExistant(2);

        $resultat = $this->inscrireParticipant->executer($coursId, 'alice');

        $this->assertSame(ResultatInscription::Inscrit, $resultat);
        // on RECHARGE depuis le repository : prouve que le cas d'usage a bien appelé enregistrer()
        $this->assertTrue($this->repository->parIdentifiant($coursId)->estInscrit('alice'));
    }

    public function test_au_dela_de_la_capacite_le_participant_va_en_liste_d_attente(): void
    {
        $coursId = $this->unCoursExistant(1);

        $this->assertSame(ResultatInscription::Inscrit, $this->inscrireParticipant->executer($coursId, 'alice'));
        $this->assertSame(ResultatInscription::EnListeAttente, $this->inscrireParticipant->executer($coursId, 'bob'));

        // l'invariant tient aussi après enregistrement : 1 inscrit, bob en attente
        $cours = $this->repository->parIdentifiant($coursId);
        $this->assertSame(1, $cours->nombreInscrits());
        $this->assertTrue($cours->estEnAttente('bob'));
    }

    public function test_inscrire_a_un_cours_inexistant_est_refuse(): void
    {
        $this->expectException(CoursIntrouvable::class);
        $this->inscrireParticipant->executer('inconnu', 'alice');
    }
}
