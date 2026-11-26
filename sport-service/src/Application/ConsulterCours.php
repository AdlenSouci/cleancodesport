<?php
namespace App\Application;

use App\Domain\Cours;

// ConsulterCours — CAS D'USAGE : lecture d'un cours par identifiant.
final class ConsulterCours
{
    public function __construct(private CoursRepository $coursRepository)
    {
    }

    public function executer(string $coursId): Cours
    {
        $cours = $this->coursRepository->parIdentifiant($coursId);
        if ($cours === null) {
            throw new CoursIntrouvable($coursId);
        }

        return $cours;
    }
}
