<?php
namespace App\Application;

use App\Domain\Cours;

// ConsulterInscription — CAS D'USAGE : lecture de l'inscription d'un participant à un cours.
//Location renvoie à cette ressource
final class ConsulterInscription
{
    public function __construct(private CoursRepository $coursRepository)
    {
    }

    public function executer(string $coursId, string $participantId): Cours
    {
        $cours = $this->coursRepository->parIdentifiant($coursId);
        if ($cours === null) {
            throw new CoursIntrouvable($coursId);
        }
        if (!$cours->estInscrit($participantId) && !$cours->estEnAttente($participantId)) {
            throw new InscriptionIntrouvable($coursId, $participantId);
        }

        return $cours;
    }
}
