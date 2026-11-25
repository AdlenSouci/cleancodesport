<?php
// Tests unitaires du value object Capacite.
//
// À faire :
// - namespace Tests\Unit\Domain
// - classe final CapaciteTest qui hérite de PHPUnit\Framework\TestCase
// - importer (use) App\Domain\Capacite et App\Domain\CapaciteInvalide
// - une méthode par test, nom qui commence par test_ et décrit la règle :
//
//   test_une_capacite_positive_est_acceptee
//       → new Capacite(20) fonctionne et sa valeur vaut 20 (assertSame)
//   test_une_capacite_nulle_est_refusee
//       → expectException(CapaciteInvalide::class) AVANT new Capacite(0)
//   test_une_capacite_negative_est_refusee
//       → même chose avec -5
//   test_deux_capacites_de_meme_valeur_sont_egales
//       → egale() renvoie true pour 20/20 et false pour 20/15
//   (si un maximum est décidé) test_une_capacite_au_dela_du_maximum_est_refusee
//
// Lancement : composer test — tout doit être vert, en moins d'une seconde.