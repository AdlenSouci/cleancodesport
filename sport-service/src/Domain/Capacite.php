<?php
namespace App\Domain;

//Classe IMMUABLE Capacite
final readonly class Capacite
{

// Refuse une capacité impossible (moins d'1 place)
public function __construct(public int $places)
    {
        if ($places < 1) {
            throw new CapaciteInvalide($places);
        }
    }
    //Fonction d'égalité
    public function egale(self $autre): bool
    {
        return $this->places === $autre->places;
    }
  
}