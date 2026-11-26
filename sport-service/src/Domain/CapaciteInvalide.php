<?php
namespace App\Domain;

final class CapaciteInvalide extends ErreurDomaine  
{
    //Constructeur avec message metier et la valeur refusée
    public function __construct(int $capacite)
    {
        parent::__construct("Une capacité doit être d'au moins 1 place, reçu : {$capacite}");
    }
}