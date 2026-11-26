<?php
// ParticipantDejaInscrit — levée quand un participant est déjà inscrit OU déjà en liste d'attente.
//
// À faire :
// - namespace App\Domain, classe FINAL, hérite de ErreurDomaine
// - message métier avec l'id du participant et celui du cours



namespace App\Domain;


final class ParticipantDejaInscrit extends ErreurDomaine
{
    public function __construct(string $idParticipant, string $idCours)
    {
        parent::__construct(
            "Le participant {$idParticipant} est déjà inscrit au cours {$idCours}"
        );
    }
    
}
