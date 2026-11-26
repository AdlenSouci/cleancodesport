<?php
namespace App\Application;

// InscriptionIntrouvable — le cours existe, mais ce participant n'y est ni inscrit ni en liste d'attente.
final class InscriptionIntrouvable extends ErreurApplication
{
    public function __construct(string $coursId, string $participantId)
    {
        parent::__construct("Le participant {$participantId} n'a pas d'inscription au cours {$coursId}");
    }
}
