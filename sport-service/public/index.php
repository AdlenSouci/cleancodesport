<?php
declare(strict_types=1);

// Composition root : câble les adapters et lance l'application HTTP (Slim).

require __DIR__ . '/../vendor/autoload.php';

use App\Application\ConsulterCours;
use App\Application\ConsulterInscription;
use App\Application\CreerCours;
use App\Application\InscrireParticipant;
use App\Application\ListerCours;
use App\Infrastructure\Http\CreerApplicationHttp;
use App\Infrastructure\Postgres\CoursRepositoryPostgres;
use App\Infrastructure\Uuid\GenerateurUuid;

// Docker fournit DATABASE_URL=postgres://app:app@postgres:5432/sport
$databaseUrl = getenv('DATABASE_URL') ?: 'postgres://app:app@localhost:5433/sport';
$parts = parse_url($databaseUrl);
if ($parts === false || !isset($parts['host'], $parts['path'])) {
    throw new RuntimeException('DATABASE_URL invalide');
}

$pdo = new PDO(
    sprintf(
        'pgsql:host=%s;port=%d;dbname=%s',
        $parts['host'],
        $parts['port'] ?? 5432,
        ltrim($parts['path'], '/')
    ),
    $parts['user'] ?? 'app',
    $parts['pass'] ?? 'app',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$repository = new CoursRepositoryPostgres($pdo); // ← avant c'était EnMemoire
$generateur = new GenerateurUuid();

$creerCours = new CreerCours($repository, $generateur);
$listerCours = new ListerCours($repository);
$consulterCours = new ConsulterCours($repository);
$inscrireParticipant = new InscrireParticipant($repository);
$consulterInscription = new ConsulterInscription($repository);

$app = CreerApplicationHttp::creer(
    $creerCours,
    $listerCours,
    $consulterCours,
    $inscrireParticipant,
    $consulterInscription,
);

$app->run();
