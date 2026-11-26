<?php
// CoursRepository — PORT : ce dont les cas d'usage ont besoin pour stocker des cours.
// C'est une INTERFACE : l'application dit QUOI, l'infrastructure dira COMMENT
// (en mémoire au TP-01, PostgreSQL au TP-02 — sans changer une ligne des cas d'usage).
//
// À faire :
// - namespace App\Application
// - `interface CoursRepository`
// - méthodes nommées dans le vocabulaire MÉTIER (exigé par le TP), par exemple :
//     enregistrer(Cours $cours): void
//     parIdentifiant(string $id): ?Cours      → null si aucun cours avec cet id
// - le `use App\Domain\Cours` est autorisé : Application dépend de Domain.
//   L'inverse est interdit.