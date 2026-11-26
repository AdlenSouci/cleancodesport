<?php
// ErreurApplication — base des erreurs propres aux CAS D'USAGE (pas aux règles métier).
// Différence avec ErreurDomaine : « cours introuvable » n'est pas une règle du cours,
// c'est un échec de l'opération demandée. Le TP-03 range ces erreurs dans application/.
//
// À faire : namespace App\Application, classe ABSTRAITE, hérite de \RuntimeException


namespace App\Application;

abstract class ErreurApplication extends \RuntimeException
{
}
