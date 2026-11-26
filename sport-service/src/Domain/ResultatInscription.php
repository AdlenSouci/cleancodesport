<?php
namespace App\Domain;

//Enumération du statut de la personne 
enum ResultatInscription: string
{
    case Inscrit = 'inscrit';
    case EnListeAttente = 'en_liste_attente';
}