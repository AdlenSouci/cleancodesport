<?php
// ErreurDomaine — classe de base de TOUTES les erreurs métier du service.
//
// À faire :
// - namespace App\Domain
// - classe ABSTRAITE (on ne lève jamais une ErreurDomaine directement)
// - hérite de \DomainException (classe native PHP)
// - aucun contenu nécessaire : elle sert à regrouper les erreurs métier,
//   pour que les couches au-dessus puissent attraper "toute erreur du domaine"
//   d'un seul catch.
//
// Règle : aucun `use` de bibliothèque externe dans src/Domain.