<?php
// Tests du cas d'usage CreerCours — avec le repo EN MÉMOIRE et le générateur PRÉVISIBLE.
// Date de début FIXE, jamais l'heure système.
//
//   test_creer_un_cours_l_enregistre_avec_un_identifiant_genere        ← NOMINAL
//       → le cours renvoyé a l'id "cours-1"
//       → parIdentifiant("cours-1") le retrouve dans le repository
//   test_creer_un_cours_avec_une_capacite_invalide_est_refuse
//       → places = 0 → CapaciteInvalide ; et le repo ne contient rien