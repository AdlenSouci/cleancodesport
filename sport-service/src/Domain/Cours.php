<?php
// Cours — AGRÉGAT : un cours collectif, ses inscrits et sa liste d'attente.
// INVARIANT : le nombre d'inscrits ne dépasse JAMAIS la capacité ;
//             au-delà, le participant va en liste d'attente.
//
// Classe final, PAS readonly (son état change).
//
// Propriétés :
// - id : string (fourni de l'extérieur)
// - intitule : string
// - capacite : Capacite
// - debut : \DateTimeImmutable                                 ← utile plus tard (planning)
// - inscrits : liste d'ids → PRIVÉ
// - listeAttente : liste d'ids, DANS L'ORDRE d'arrivée → PRIVÉ
//
// Méthodes métier :
// - inscrire(string $participantId): ResultatInscription
//     1. déjà inscrit ou déjà en attente → ParticipantDejaInscrit
//     2. reste de la place → ajouté aux inscrits → Inscrit
//     3. sinon → ajouté à la FIN de la liste d'attente → EnListeAttente
//
// - desinscrire(string $participantId): void
//     retire le participant (des inscrits ou de la liste d'attente).
//     Ne promeut PERSONNE automatiquement : la promotion est une étape séparée
//     (déclenchée plus tard par l'événement « place libérée », traité par le worker).
//     Participant inconnu → à décider (erreur ParticipantNonInscrit, ou rien).
//
// - promouvoirPremierEnAttente(): ?string
//     SI une place est libre ET la liste d'attente n'est pas vide :
//        le premier de la liste passe dans les inscrits → renvoie son id
//     SINON : ne fait RIEN → renvoie null
//   → C'EST LA PARADE AU PIÈGE : appelée deux fois pour une seule place libérée,
//     la 2e trouve le cours plein et ne promeut personne. L'invariant tient.
//
// Méthodes de lecture : nombreInscrits(), placesRestantes(), estInscrit($id),
//                       estEnAttente($id), positionEnAttente($id)
//
// Règle : aucun `use` externe, aucun new DateTime() dans ce fichier.


namespace App\Domain;

// Cours — AGRÉGAT : un cours collectif, ses inscrits et sa liste d'attente.
final class Cours
{
    private string $id;
    private string $intitule;
    private Capacite $capacite;
    private \DateTimeImmutable $debut;
    private array $inscrits;
    private array $listeAttente;


    public function __construct(string $id, string $intitule, Capacite $capacite, \DateTimeImmutable $debut)
    {
        $this->id = $id;
        $this->intitule = $intitule;
        $this->capacite = $capacite;
        $this->debut = $debut;
        $this->inscrits = [];
        $this->listeAttente = [];
    }

    public function inscrire(string $participantId): ResultatInscription
    {
        // 1. déjà inscrit ou déjà en attente → ParticipantDejaInscrit
        if (in_array($participantId, $this->inscrits) || in_array($participantId, $this->listeAttente)) {
            throw new ParticipantDejaInscrit($participantId, $this->id);
        }
        // 2. reste de la place → ajouté aux inscrits → Inscrit
        if (count($this->inscrits) < $this->capacite->places) {
            $this->inscrits[] = $participantId;
            return ResultatInscription::Inscrit;
        }
        // 3. sinon → ajouté à la FIN de la liste d'attente → EnListeAttente
        $this->listeAttente[] = $participantId;
        return ResultatInscription::EnListeAttente;
    }

    public function desinscrire(string $participantId): void
    {
        $this->inscrits = array_diff($this->inscrits, [$participantId]);
        $this->listeAttente = array_diff($this->listeAttente, [$participantId]);
    }

    public function promouvoirPremierEnAttente(): ?string
    {
    if (count($this->inscrits) >= $this->capacite->places || $this->listeAttente === []) {
        return null;
    }

    $id = array_shift($this->listeAttente);
    $this->inscrits[] = $id;
    return $id;
    }

    public function nombreInscrits(): int
    {
        return count($this->inscrits);
    }
    public function placesRestantes(): int
    {
        return $this->capacite->places - count($this->inscrits);
    }
    public function estInscrit(string $id): bool
    {
        return in_array($id, $this->inscrits);
    }
    public function estEnAttente(string $id): bool
    {
        return in_array($id, $this->listeAttente);
    }
    public function positionEnAttente(string $id): int
    {
        return array_search($id, $this->listeAttente) + 1;
    }
    
    
    
    
}
