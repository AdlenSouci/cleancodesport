<?php

declare(strict_types=1);

namespace App\Domain;

final class Cours
{
    private const STATUT_INSCRIT = 'INSCRIT';
    private const STATUT_LISTE_ATTENTE = 'LISTE_ATTENTE';

    /** @var list<string> */
    private array $inscrits = [];

    /** @var list<string> */
    private array $listeAttente = [];

    public function __construct(
        public readonly string $id,
        public readonly Capacite $capacite
    ) {}

    public function inscrire(string $participantId): string
    {
        if (in_array($participantId, $this->inscrits, true) || in_array($participantId, $this->listeAttente, true)) {
            throw new ErreurParticipantDejaInscrit();
        }

        if (count($this->inscrits) < $this->capacite->valeur()) {
            $this->inscrits[] = $participantId;
            return self::STATUT_INSCRIT;
        }

        $this->listeAttente[] = $participantId;
        return self::STATUT_LISTE_ATTENTE;
    }

    public function desinscrire(string $participantId): ?string
    {
        $indexInscrit = array_search($participantId, $this->inscrits, true);
        if ($indexInscrit !== false) {
            unset($this->inscrits[$indexInscrit]);
            $this->inscrits = array_values($this->inscrits);

            if ($this->listeAttente !== []) {
                $promu = array_shift($this->listeAttente);
                $this->inscrits[] = $promu;
                return $promu;
            }

            return null;
        }

        $indexAttente = array_search($participantId, $this->listeAttente, true);
        if ($indexAttente !== false) {
            unset($this->listeAttente[$indexAttente]);
            $this->listeAttente = array_values($this->listeAttente);
        }

        return null;
    }

    /** @return list<string> */
    public function inscrits(): array
    {
        return $this->inscrits;
    }

    /** @return list<string> */
    public function listeAttente(): array
    {
        return $this->listeAttente;
    }
}