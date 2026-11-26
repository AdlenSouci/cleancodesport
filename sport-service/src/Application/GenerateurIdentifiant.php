<?php
// GenerateurIdentifiant — PORT : fournit un nouvel identifiant unique.
// Pourquoi un port ? En production ce sera un UUID aléatoire ; dans les tests,
// on veut des ids PRÉVISIBLES ("cours-1", "cours-2"…) pour pouvoir les vérifier.
//
// À faire :
// - namespace App\Application, `interface GenerateurIdentifiant`
// - une méthode : nouveau(): string

namespace App\Application;

interface GenerateurIdentifiant
{
    public function nouveau(): string;
}