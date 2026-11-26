<?php
namespace Tests\Unit\Application;

use App\Application\CreerCours;
use App\Domain\CapaciteInvalide;
use App\Infrastructure\Memoire\CoursRepositoryEnMemoire;
use PHPUnit\Framework\TestCase;
use Tests\Doublures\GenerateurIdentifiantPrevisible;

final class CreerCoursTest extends TestCase
{
    private function debut(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('2026-12-01 10:00:00');
    }

    public function test_creer_un_cours_l_enregistre_avec_un_identifiant_genere(): void
    {
        $repository = new CoursRepositoryEnMemoire();
        $generateur = new GenerateurIdentifiantPrevisible();
        $creerCours = new CreerCours($repository, $generateur);

        $cours = $creerCours->executer('Yoga', 10, $this->debut());

        $this->assertSame('cours-1', $cours->id());
        $enregistre = $repository->parIdentifiant('cours-1');
        $this->assertNotNull($enregistre);
        $this->assertSame(10, $enregistre->placesRestantes());
    }

    public function test_creer_un_cours_avec_une_capacite_invalide_est_refuse(): void
    {
        $repository = new CoursRepositoryEnMemoire();
        $creerCours = new CreerCours($repository, new GenerateurIdentifiantPrevisible());

        $this->expectException(CapaciteInvalide::class);
        $creerCours->executer('Yoga', 0, $this->debut());
    }

    public function test_un_cours_refuse_n_est_pas_enregistre(): void
    {
        $repository = new CoursRepositoryEnMemoire();
        $creerCours = new CreerCours($repository, new GenerateurIdentifiantPrevisible());

        try {
            $creerCours->executer('Yoga', 0, $this->debut());
            $this->fail('CapaciteInvalide aurait dû être levée');
        } catch (CapaciteInvalide) {
            // attendu
        }

        $this->assertNull($repository->parIdentifiant('cours-1'));
    }
}
