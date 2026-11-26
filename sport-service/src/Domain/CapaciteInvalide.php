<?php
// CapaciteInvalide — levée quand on tente de créer une Capacite avec une valeur interdite.
//
// À faire :
// - namespace App\Domain
// - classe FINAL, hérite de ErreurDomaine
// - le message doit parler métier et inclure la valeur refusée
//   (ex. "Une capacité doit être d'au moins 1 place, reçu : 0")

namespace App\Domain;

final class CapaciteInvalide extends ErreurDomaine  
{
    public function __construct(int $capacite)
    {
        parent::__construct("Une capacité doit être d'au moins 1 place, reçu : {$capacite}");
    }
}