<?php
// CreerCours — CAS D'USAGE 1 : créer la ressource principale.
//
// À faire :
// - namespace App\Application, classe FINAL
// - dépendances reçues par le CONSTRUCTEUR (exigé par le TP), jamais créées ici :
//     CoursRepository, GenerateurIdentifiant
// - une méthode executer(...) avec des données SIMPLES en entrée (pas d'objet requête) :
//     string $intitule, int $places, \DateTimeImmutable $debut
// - étapes :
//     1. construire la Capacite (si invalide → CapaciteInvalide remonte telle quelle)
//     2. demander un id au générateur
//     3. construire le Cours
//     4. l'enregistrer dans le repository
//     5. renvoyer le Cours (un objet du domaine)
// - AUCUNE règle métier ici : elles sont dans Capacite et Cours.
// - Interdit : tout `use App\Infrastructure\...` (critère de réussite du TP).