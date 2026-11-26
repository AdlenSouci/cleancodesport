<?php
namespace App\Application;

// ListerCours — CAS D'USAGE : collection paginée (offset).
final class ListerCours
{
    public function __construct(private CoursRepository $coursRepository)
    {
    }

    public function executer(int $limit, int $offset): PageCours
    {
        if ($limit < 1 || $limit > 100) {
            throw new EntreeInvalide('limit doit être entre 1 et 100');
        }
        if ($offset < 0) {
            throw new EntreeInvalide('offset doit être ≥ 0');
        }

        return new PageCours(
            $this->coursRepository->paginer($limit, $offset),
            $this->coursRepository->compter(),
            $limit,
            $offset,
        );
    }
}
