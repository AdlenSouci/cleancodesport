<?php
namespace Tests\Unit\Infrastructure;

use App\Domain\Cours;
use App\Domain\Capacite;
use PHPUnit\Framework\TestCase;
use App\Infrastructure\Memoire\CoursRepositoryEnMemoire;

final class CoursRepositoryEnMemoireTest extends TestCase
{
    private function unCours(int $places): Cours
    {
        return new Cours(
            'cours-1',
            'Yoga',
            new Capacite($places),
            new \DateTimeImmutable('2026-12-01 10:00:00')
        );
    }

    public function test_un_cours_enregistre_peut_etre_relu(): void
    {
        $repository = new CoursRepositoryEnMemoire();
        $cours = $this->unCours(1);

        $repository->enregistrer($cours);
        $relu = $repository->parIdentifiant('cours-1');

        $this->assertNotNull($relu);
        $this->assertSame('cours-1', $relu->id());
    }

    public function test_un_id_inconnu_renvoie_null(): void
    {
        $repository = new CoursRepositoryEnMemoire();
        $repository->enregistrer($this->unCours(1));

        $this->assertNull($repository->parIdentifiant('inconnu'));
    }

    public function test_modifier_un_cours_charge_ne_modifie_pas_le_stockage_sans_enregistrer(): void
    {
        $repository = new CoursRepositoryEnMemoire();
        $repository->enregistrer($this->unCours(2));

        $charge = $repository->parIdentifiant('cours-1');
        $charge->inscrire('alice');

        $recharge = $repository->parIdentifiant('cours-1');
        $this->assertFalse($recharge->estInscrit('alice'));
    }

    public function test_un_cours_modifie_puis_enregistre_est_bien_persiste(): void
    {
        $repository = new CoursRepositoryEnMemoire();
        $repository->enregistrer($this->unCours(2));

        $charge = $repository->parIdentifiant('cours-1');
        $charge->inscrire('alice');
        $repository->enregistrer($charge);

        // cette fois, la modification a été enregistrée : elle doit être relue
        $this->assertTrue($repository->parIdentifiant('cours-1')->estInscrit('alice'));
    }
}
