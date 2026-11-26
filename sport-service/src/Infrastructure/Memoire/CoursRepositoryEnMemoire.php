<?php
// CoursRepositoryEnMemoire — ADAPTER : implémente le port CoursRepository avec un tableau PHP.
//
// À faire :
// - namespace App\Infrastructure\Memoire
// - `final class CoursRepositoryEnMemoire implements CoursRepository`
// - un tableau privé indexé par id de cours
//
// RÈGLE CLÉ DU TP : se comporter comme une VRAIE persistance → stocker des COPIES.
// - enregistrer($cours)   : range un CLONE (`clone $cours`), pas l'objet reçu
// - parIdentifiant($id)   : renvoie un CLONE de ce qui est stocké (ou null)
// Pourquoi : si on rangeait l'objet lui-même, modifier un cours chargé
// modifierait le stockage SANS appeler enregistrer() → les tests passeraient
// en mémoire mais le code serait faux avec PostgreSQL (le cas d'usage
// qui oublie d'enregistrer ne serait jamais détecté).
// Un clone simple suffit : les propriétés de Cours sont des chaînes, des tableaux
// et des objets immuables (Capacite, DateTimeImmutable).