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