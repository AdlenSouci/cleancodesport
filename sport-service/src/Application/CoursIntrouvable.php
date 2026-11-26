<?php
// CoursIntrouvable — levée quand un cas d'usage reçoit l'id d'un cours qui n'existe pas.
//
// À faire : namespace App\Application, classe FINAL, hérite de ErreurApplication,
//           message avec l'id recherché.

namespace App\Application;

final class CoursIntrouvable extends ErreurApplication
{
    public function __construct(string $id)
    {
        parent::__construct("Le cours {$id} n'existe pas");
    }
}