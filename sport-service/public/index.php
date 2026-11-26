<?php
declare(strict_types=1);

// Point d'entrée HTTP TEMPORAIRE.
// Il répond seulement à GET /sante, pour que le conteneur respecte le contrat
// d'intégration du README (« GET /sante répond 200 »).
// Au TP-02, il sera remplacé par la composition root : connexion PostgreSQL,
// adapters, cas d'usage et application HTTP (Slim).

require __DIR__ . '/../vendor/autoload.php';

$chemin = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $chemin === '/sante') {
    header('Content-Type: application/json');
    echo json_encode(['statut' => 'ok']);
    return;
}

http_response_code(404);
