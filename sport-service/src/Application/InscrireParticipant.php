<?php
// InscrireParticipant — CAS D'USAGE 2 : l'action qui met l'INVARIANT à l'épreuve.
//
// À faire :
// - namespace App\Application, classe FINAL
// - dépendance par le constructeur : CoursRepository
// - executer(string $coursId, string $participantId): ResultatInscription
// - étapes :
//     1. charger le cours (parIdentifiant) ; null → CoursIntrouvable
//     2. appeler $cours->inscrire($participantId)   ← l'agrégat applique l'invariant
//     3. ENREGISTRER le cours modifié                ← sans ça, rien n'est persisté
//     4. renvoyer le résultat (Inscrit ou EnListeAttente)
// - Le cas d'usage ORCHESTRE (charger, appeler, enregistrer) ; il ne DÉCIDE rien.
//   Pas de « if (count(...) < capacite) » ici : ce serait l'invariant au mauvais endroit.



namespace App\Application;

use App\Domain\Capacite;
use App\Domain\Cours;

final class CreerCours
{
    private CoursRepository $coursRepository;
    private GenerateurIdentifiant $generateurIdentifiant;

    public function __construct(
        CoursRepository $coursRepository,
        GenerateurIdentifiant $generateurIdentifiant
    ) {
        $this->coursRepository = $coursRepository;
        $this->generateurIdentifiant = $generateurIdentifiant;
    }

    public function executer(string $intitule, int $places, \DateTimeImmutable $debut): Cours
    {
        $capacite = new Capacite($places);
        $id = $this->generateurIdentifiant->nouveau();
        $cours = new Cours($id, $intitule, $capacite, $debut);
        $this->coursRepository->enregistrer($cours);

        return $cours;
    }
}