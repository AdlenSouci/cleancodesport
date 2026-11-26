<?php
// ResultatInscription — ce que renvoie Cours::inscrire().
//
// À faire :
// - namespace App\Domain
// - une ENUM PHP (mot-clé `enum`, PHP 8.1+) avec deux cas :
//     Inscrit        → le participant a une place
//     EnListeAttente → le cours était plein, il attend
// - Ce n'est PAS une erreur : être en liste d'attente est un cas normal du métier.