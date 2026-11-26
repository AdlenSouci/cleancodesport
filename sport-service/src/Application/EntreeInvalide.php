<?php
namespace App\Application;

// EntreeInvalide — paramètres de requête hors contrat (pagination, corps JSON…).
final class EntreeInvalide extends ErreurApplication
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
