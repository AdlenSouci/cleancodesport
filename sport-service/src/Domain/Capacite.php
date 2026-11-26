<?php
// Capacite — VALUE OBJECT : le nombre maximum de participants d'un cours.
//
// Les 3 propriétés d'un value object (exigées par le TP) :
// 1. IMMUABLE : classe `final readonly`, aucune méthode ne modifie l'objet.
// 2. TOUJOURS VALIDE : la vérification se fait dans le constructeur ;
//    une valeur interdite lève CapaciteInvalide → un objet invalide ne peut pas exister.
// 3. COMPARÉ PAR VALEUR : méthode egale(self $autre): bool qui compare les nombres,
//    pas les instances.
//
// À faire :
// - namespace App\Domain
// - une propriété entière publique en lecture seule (ex. $places)
// - règle : au moins 1 place
// - (à décider ensemble) un maximum ? Si oui, il se vérifie ici aussi, avec son test.
//
// Règle : pas de dépendance externe, pas d'accès à l'horloge.

namespace App\Domain;

final readonly class Capacite
{
    public function __construct(public int $places)
    {
        if ($places < 1) {
            throw new CapaciteInvalide($places);
        }
    }
    public function egale(self $autre): bool
    {
        return $this->places === $autre->places;
    }
  
}