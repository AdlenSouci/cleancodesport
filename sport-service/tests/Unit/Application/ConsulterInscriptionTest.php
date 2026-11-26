<?php
namespace Tests\Unit\Application;

use App\Application\ConsulterInscription;
use App\Application\CoursIntrouvable;
use App\Application\CreerCours;
use App\Application\InscriptionIntrouvable;
use App\Application\InscrireParticipant;
use App\Infrastructure\Memoire\CoursRepositoryEnMemoire;
use PHPUnit\Framework\TestCase;
use Tests\Doublures\GenerateurIdentifiantPrevisible;

final class ConsulterInscriptionTest extends TestCase
{
    private CoursRepositoryEnMemoire $repository;
    private ConsulterInscription $consulterInscription;

    protected function setUp(): void
    {
        $this->repository = new CoursRepositoryEnMemoire();
        $this->consulterInscription = new ConsulterInscription($this->repository);
    }

    // Un cours de $places places ("cours-1") où les participants donnés sont inscrits dans l'ordre
    private function unCoursAvec(int $places, string ...$participants): void
    {
        (new CreerCours($this->repository, new GenerateurIdentifiantPrevisible()))
            ->executer('Yoga', $places, new \DateTimeImmutable('2026-12-01 10:00:00'));
        $inscrire = new InscrireParticipant($this->repository);
        foreach ($participants as $participant) {
            $inscrire->executer('cours-1', $participant);
        }
    }

    public function test_renvoie_le_cours_d_un_participant_inscrit(): void
    {
        $this->unCoursAvec(2, 'alice');

        $cours = $this->consulterInscription->executer('cours-1', 'alice');

        $this->assertTrue($cours->estInscrit('alice'));
    }

    public function test_renvoie_le_cours_d_un_participant_en_liste_d_attente(): void
    {
        $this->unCoursAvec(1, 'alice', 'bob');

        $cours = $this->consulterInscription->executer('cours-1', 'bob');

        $this->assertSame(1, $cours->positionEnAttente('bob'));
    }

    public function test_un_participant_absent_du_cours_est_introuvable(): void
    {
        $this->unCoursAvec(2, 'alice');

        $this->expectException(InscriptionIntrouvable::class);
        $this->consulterInscription->executer('cours-1', 'zoe');
    }

    public function test_un_cours_inexistant_est_introuvable(): void
    {
        $this->expectException(CoursIntrouvable::class);
        $this->consulterInscription->executer('inconnu', 'alice');
    }
}
