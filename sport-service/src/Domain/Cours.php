<?php
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

    //Constructeur pour créer un cours vide
    public function __construct(string $id, string $intitule, Capacite $capacite, \DateTimeImmutable $debut)
    {
        $this->id = $id;
        $this->intitule = $intitule;
        $this->capacite = $capacite;
        $this->debut = $debut;
        $this->inscrits = [];
        $this->listeAttente = [];
    }

    //Inscrit la personne au cours choisi
    public function inscrire(string $participantId): ResultatInscription
    {
        if (in_array($participantId, $this->inscrits) || in_array($participantId, $this->listeAttente)) {
            throw new ParticipantDejaInscrit($participantId, $this->id);
        }
        if (count($this->inscrits) < $this->capacite->places) {
            $this->inscrits[] = $participantId;
            return ResultatInscription::Inscrit;
        }
        $this->listeAttente[] = $participantId;
        return ResultatInscription::EnListeAttente;
    }


    //désinscrire un utilisateur met à jour la file d'attente
    public function desinscrire(string $participantId): void
    {
        $this->inscrits = array_values(array_diff($this->inscrits, [$participantId]));
        $this->listeAttente = array_values(array_diff($this->listeAttente, [$participantId]));
    }
    
    //Insère le premier de la liste d'attente dans les inscrits du cours
    public function promouvoirPremierEnAttente(): ?string
    {
        if (count($this->inscrits) >= $this->capacite->places || $this->listeAttente === []) {
            return null;
        }
    $id = array_shift($this->listeAttente);
    $this->inscrits[] = $id;
    return $id;
    }

    //Retourne le nombre d'inscrit
    public function nombreInscrits(): int
    {
        return count($this->inscrits);
    }

    //Retourne le nombre de places restantes
    public function placesRestantes(): int
    {
        return $this->capacite->places - count($this->inscrits);
    }

    //Retourne si la personne est inscrite
    public function estInscrit(string $id): bool
    {
        return in_array($id, $this->inscrits);
    }

    //Retourne si la personne est dans la liste d'attente
    public function estEnAttente(string $id): bool
    {
        return in_array($id, $this->listeAttente);
    }

    //Retourne la position de la personne en attente
    public function positionEnAttente(string $id): ?int
    {
        $file = array_search($id, $this->listeAttente);
        if ($file === false) {
            return null;
        }

        return $file + 1 ;
    }
}
