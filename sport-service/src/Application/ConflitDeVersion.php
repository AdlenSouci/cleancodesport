<?php
namespace App\Application;

// ConflitDeVersion — écriture concurrente refusée (verrouillage optimiste).
final class ConflitDeVersion extends ErreurApplication
{
    public function __construct(string $coursId)
    {
        parent::__construct("Conflit de version sur le cours {$coursId}");
    }
}
