<?php
// Tests unitaires de l'agrégat Cours.
// - fabrique privée unCours(int $places) ; date de début FIXE
//
//   test_un_participant_est_inscrit_quand_il_reste_de_la_place
//       → résultat Inscrit, nombreInscrits() = 1
//   test_au_dela_de_la_capacite_le_participant_va_en_liste_d_attente   ← PROUVE L'INVARIANT
//       → cours de 1 place : alice Inscrit, bob EnListeAttente, nombreInscrits() reste 1
//   test_la_liste_d_attente_respecte_l_ordre_d_arrivee
//       → bob puis carol en attente : positions 1 et 2
//   test_un_participant_ne_peut_pas_s_inscrire_deux_fois
//       → déjà inscrit → ParticipantDejaInscrit
//   test_un_participant_deja_en_attente_ne_peut_pas_se_reinscrire
//   test_une_desinscription_libere_une_place_sans_promouvoir
//       → alice se désinscrit : 1 place libre, bob toujours en attente
//   test_la_promotion_fait_passer_le_premier_en_attente
//       → après désinscription + promotion : bob est inscrit, renvoie "bob"
//   test_une_promotion_rejouee_ne_promeut_personne                     ← PROUVE LA PARADE AU PIÈGE
//       → 1 place libérée, bob ET carol en attente
//       → 1re promotion : bob ; 2e promotion : null, carol toujours en attente,
//         nombreInscrits() ne dépasse pas la capacité
//   test_sans_place_libre_la_promotion_ne_fait_rien

namespace Tests\Unit\Domain;

use App\Domain\Cours;
use App\Domain\Capacite;
use App\Domain\ResultatInscription;
use App\Domain\ParticipantDejaInscrit;
use PHPUnit\Framework\TestCase;

final class CoursTest extends TestCase
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

    public function test_un_participant_est_inscrit_quand_il_reste_de_la_place(): void
    {
        $cours = $this->unCours(2);
        $resultat = $cours->inscrire('alice');

        $this->assertSame(ResultatInscription::Inscrit, $resultat);
        $this->assertSame(1, $cours->nombreInscrits());
    }

    public function test_au_dela_de_la_capacite_le_participant_va_en_liste_d_attente(): void
    {
        $cours = $this->unCours(1);

        $this->assertSame(ResultatInscription::Inscrit, $cours->inscrire('alice'));
        $this->assertSame(ResultatInscription::EnListeAttente, $cours->inscrire('bob'));
        $this->assertSame(1, $cours->nombreInscrits());
    }

    public function test_la_liste_d_attente_respecte_l_ordre_d_arrivee(): void
    {
        $cours = $this->unCours(1);
        $cours->inscrire('alice');
        $cours->inscrire('bob');
        $cours->inscrire('carol');

        $this->assertSame(1, $cours->positionEnAttente('bob'));
        $this->assertSame(2, $cours->positionEnAttente('carol'));
    }

    public function test_un_participant_ne_peut_pas_s_inscrire_deux_fois(): void
    {
        $cours = $this->unCours(2);
        $cours->inscrire('alice');

        $this->expectException(ParticipantDejaInscrit::class);
        $cours->inscrire('alice');
    }

    public function test_un_participant_deja_en_attente_ne_peut_pas_se_reinscrire(): void
    {
        $cours = $this->unCours(1);
        $cours->inscrire('alice');
        $cours->inscrire('bob');

        $this->expectException(ParticipantDejaInscrit::class);
        $cours->inscrire('bob');
    }

    public function test_une_desinscription_libere_une_place_sans_promouvoir(): void
    {
        $cours = $this->unCours(1);
        $cours->inscrire('alice');
        $cours->inscrire('bob');
        $cours->desinscrire('alice');

        $this->assertSame(1, $cours->placesRestantes());
        $this->assertTrue($cours->estEnAttente('bob'));
    }

    public function test_la_promotion_fait_passer_le_premier_en_attente(): void
    {
        $cours = $this->unCours(1);
        $cours->inscrire('alice');
        $cours->inscrire('bob');
        $cours->desinscrire('alice');

        $id = $cours->promouvoirPremierEnAttente();

        $this->assertSame('bob', $id);
        $this->assertTrue($cours->estInscrit('bob'));
    }

    public function test_une_promotion_rejouee_ne_promeut_personne(): void
    {
        $cours = $this->unCours(1);
        $cours->inscrire('alice');
        $cours->inscrire('bob');
        $cours->inscrire('carol');
        $cours->desinscrire('alice');

        $this->assertSame('bob', $cours->promouvoirPremierEnAttente());
        $this->assertNull($cours->promouvoirPremierEnAttente());
        $this->assertTrue($cours->estEnAttente('carol'));
        $this->assertSame(1, $cours->nombreInscrits());
    }

    public function test_sans_place_libre_la_promotion_ne_fait_rien(): void
    {
        $cours = $this->unCours(1);
        $cours->inscrire('alice');
        $cours->inscrire('bob');

        $this->assertNull($cours->promouvoirPremierEnAttente());
        $this->assertTrue($cours->estEnAttente('bob'));
        $this->assertSame(1, $cours->nombreInscrits());
    }
}
