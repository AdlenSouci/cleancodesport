<?php
// Tests de l'adapter en mémoire : il doit se comporter comme une vraie persistance.
//
//   test_un_cours_enregistre_peut_etre_relu
//   test_un_id_inconnu_renvoie_null
//   test_modifier_un_cours_charge_ne_modifie_pas_le_stockage_sans_enregistrer   ← LA RÈGLE DES COPIES
//       → enregistrer un cours ; le recharger ; y inscrire "alice" SANS enregistrer
//       → le recharger à nouveau : "alice" n'est PAS inscrite