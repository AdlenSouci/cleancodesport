<?php


namespace App\Domain;

enum ResultatInscription: string
{
    case Inscrit = 'inscrit';
    case EnListeAttente = 'en_liste_attente';
}