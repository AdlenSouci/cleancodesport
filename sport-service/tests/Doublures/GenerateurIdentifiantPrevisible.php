<?php
// GenerateurIdentifiantPrevisible — DOUBLURE de test du port GenerateurIdentifiant.
// Les starters TS/Python la fournissent ; en PHP, on l'écrit (exigé par le TP).
//
// À faire :
// - namespace Tests\Doublures
// - implements App\Application\GenerateurIdentifiant
// - renvoie "cours-1", puis "cours-2", etc. (un compteur privé)
// - déterministe : deux exécutions des tests donnent les mêmes ids.

namespace Tests\Doublures;
use App\Application\GenerateurIdentifiant;

final class GenerateurIdentifiantPrevisible implements GenerateurIdentifiant
{
	private int $compteur = 0;
	public function nouveau() : string
	{
		$this->compteur++;
		return "cours-" . $this->compteur;
	}
}

