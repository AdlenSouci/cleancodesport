<?php
namespace App\Domain;

//classe qui lève l'erreur comme quoi le participant est déjà inscrit / déjà en liste d'attente
final class ParticipantDejaInscrit extends ErreurDomaine
{
    public function __construct(string $idParticipant, string $idCours)
    {
        parent::__construct(
            "Le participant {$idParticipant} est déjà inscrit au cours {$idCours}"
        );
    }
    
}
