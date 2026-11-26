<?php
namespace App\Application;

use App\Domain\Cours;

// PageCours — résultat de pagination offset pour ListerCours.
final class PageCours
{
    /** @param list<Cours> $items */
    public function __construct(
        public readonly array $items,
        public readonly int $total,
        public readonly int $limit,
        public readonly int $offset,
    ) {
    }
}
