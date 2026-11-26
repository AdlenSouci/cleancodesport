<?php
// Tests du cas d'usage InscrireParticipant.
// Préparer un cours existant : le créer via CreerCours, ou l'enregistrer directement dans le repo.
//
//   test_un_participant_est_inscrit_et_l_inscription_est_enregistree   ← NOMINAL
//       → résultat Inscrit
//       → en RECHARGEANT le cours depuis le repo, le participant est inscrit
//         (prouve que le cas d'usage a bien appelé enregistrer())
//   test_au_dela_de_la_capacite_le_participant_va_en_liste_d_attente   ← INVARIANT
//       → cours de 1 place : 1er Inscrit, 2e EnListeAttente ; rechargé : 1 inscrit
//   test_inscrire_a_un_cours_inexistant_est_refuse                     ← INTROUVABLE
//       → id "inconnu" → CoursIntrouvable