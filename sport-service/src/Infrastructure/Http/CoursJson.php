<?php
namespace App\Infrastructure\Http;

use App\Application\PageCours;
use App\Domain\Cours;
use App\Domain\ResultatInscription;

// CoursJson — mapping domaine → JSON du contrat OpenAPI (couche HTTP uniquement).
final class CoursJson
{
    /** @return array{id: string, intitule: string, places: int, debut: string, nombreInscrits: int, placesRestantes: int} */
    public static function depuisCours(Cours $cours): array
    {
        return [
            'id' => $cours->id(),
            'intitule' => $cours->intitule(),
            'places' => $cours->places(),
            'debut' => $cours->debut()->format(\DateTimeInterface::ATOM),
            'nombreInscrits' => $cours->nombreInscrits(),
            'placesRestantes' => $cours->placesRestantes(),
        ];
    }

    /** @return array{items: list<array>, total: int, limit: int, offset: int} */
    public static function depuisPage(PageCours $page): array
    {
        return [
            'items' => array_map(self::depuisCours(...), $page->items),
            'total' => $page->total,
            'limit' => $page->limit,
            'offset' => $page->offset,
        ];
    }

    /**
     * Réponse du POST d'inscription : ce que renvoie le cas d'usage InscrireParticipant.
     * @return array{participantId: string, statut: string}
     */
    public static function depuisResultat(string $participantId, ResultatInscription $resultat): array
    {
        return ['participantId' => $participantId, 'statut' => $resultat->value];
    }

    /**
     * Représentation de la ressource inscription (GET) : le statut, plus le rang
     * dans la liste d'attente (null si le participant est inscrit).
     * @return array{participantId: string, statut: string, position: ?int}
     */
    public static function depuisInscription(Cours $cours, string $participantId): array
    {
        $statut = $cours->estInscrit($participantId)
            ? ResultatInscription::Inscrit
            : ResultatInscription::EnListeAttente;

        return [
            'participantId' => $participantId,
            'statut' => $statut->value,
            'position' => $cours->positionEnAttente($participantId),
        ];
    }
}
