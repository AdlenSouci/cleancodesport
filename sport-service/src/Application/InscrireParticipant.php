<?php
namespace App\Application;

use App\Domain\ResultatInscription;

// InscrireParticipant — CAS D'USAGE 2 : l'action qui met l'INVARIANT à l'épreuve.
// Il ORCHESTRE (charger, appeler le domaine, enregistrer) mais ne DÉCIDE rien :
// la règle « inscrits ≤ capacité, sinon liste d'attente » vit dans l'agrégat Cours.
final class InscrireParticipant
{
    private CoursRepository $coursRepository;

    // Dépendance reçue par le constructeur : le cas d'usage ignore s'il parle
    // à la mémoire (tests) ou à PostgreSQL (TP-02).
    public function __construct(CoursRepository $coursRepository)
    {
        $this->coursRepository = $coursRepository;
    }

    public function executer(string $coursId, string $participantId): ResultatInscription
    {
        $cours = $this->coursRepository->parIdentifiant($coursId);
        if ($cours === null) {
            throw new CoursIntrouvable($coursId);
        }

        // l'agrégat applique l'invariant (et refuse un doublon)
        $resultat = $cours->inscrire($participantId);

        // sans cet enregistrement, l'inscription serait perdue
        $this->coursRepository->enregistrer($cours);

        return $resultat;
    }
}
