<?php
namespace App\Infrastructure\Http;

use App\Application\ConsulterCours;
use App\Application\ConsulterInscription;
use App\Application\CreerCours;
use App\Application\EntreeInvalide;
use App\Application\InscrireParticipant;
use App\Application\ListerCours;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

// RoutesCours — ADAPTER HTTP : traduit les requêtes REST en appels de cas d'usage.
final class RoutesCours
{
    public static function enregistrer(
        App $app,
        CreerCours $creerCours,
        ListerCours $listerCours,
        ConsulterCours $consulterCours,
        InscrireParticipant $inscrireParticipant,
        ConsulterInscription $consulterInscription,
    ): void {
        $app->get('/sante', function (Request $request, Response $response): Response {
            return self::json($response, ['statut' => 'ok']);
        });

        $app->get('/openapi.yaml', function (Request $request, Response $response): Response {
            $chemin = dirname(__DIR__, 3) . '/openapi.yaml';
            $response->getBody()->write((string) file_get_contents($chemin));
            return $response->withHeader('Content-Type', 'application/yaml');
        });

        $app->get('/docs', function (Request $request, Response $response): Response {
            $html = <<<'HTML'
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8"/>
  <title>sport-service — OpenAPI</title>
  <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css"/>
</head>
<body>
  <div id="swagger-ui"></div>
  <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
  <script>
    SwaggerUIBundle({ url: '/openapi.yaml', dom_id: '#swagger-ui' });
  </script>
</body>
</html>
HTML;
            $response->getBody()->write($html);
            return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
        });

        $app->get('/cours', function (Request $request, Response $response) use ($listerCours): Response {
            $q = $request->getQueryParams();
            $limit = isset($q['limit']) ? (int) $q['limit'] : 20;
            $offset = isset($q['offset']) ? (int) $q['offset'] : 0;
            $page = $listerCours->executer($limit, $offset);
            return self::json($response, CoursJson::depuisPage($page));
        });

        $app->post('/cours', function (Request $request, Response $response) use ($creerCours): Response {
            $corps = self::corpsJson($request);
            $intitule = self::chaineRequise($corps, 'intitule');
            $places = self::entierRequis($corps, 'places');
            $debut = self::dateRequise($corps, 'debut');

            $cours = $creerCours->executer($intitule, $places, $debut);

            return self::json(
                $response->withHeader('Location', '/cours/' . $cours->id())->withStatus(201),
                CoursJson::depuisCours($cours),
            );
        });

        $app->get('/cours/{coursId}', function (Request $request, Response $response, array $args) use ($consulterCours): Response {
            $cours = $consulterCours->executer((string) $args['coursId']);
            return self::json($response, CoursJson::depuisCours($cours));
        });

        // Une inscription est une sous-ressource créée : 201 + Location vers la ressource créée
        $app->post('/cours/{coursId}/inscriptions', function (Request $request, Response $response, array $args) use ($inscrireParticipant): Response {
            $coursId = (string) $args['coursId'];
            $corps = self::corpsJson($request);
            $participantId = self::chaineRequise($corps, 'participantId');

            $resultat = $inscrireParticipant->executer($coursId, $participantId);

            return self::json(
                $response
                    ->withHeader('Location', "/cours/{$coursId}/inscriptions/{$participantId}")
                    ->withStatus(201),
                CoursJson::depuisResultat($participantId, $resultat),
            );
        });

        $app->get('/cours/{coursId}/inscriptions/{participantId}', function (Request $request, Response $response, array $args) use ($consulterInscription): Response {
            $participantId = (string) $args['participantId'];
            $cours = $consulterInscription->executer((string) $args['coursId'], $participantId);
            return self::json($response, CoursJson::depuisInscription($cours, $participantId));
        });
    }

    /** @return array<string, mixed> */
    private static function corpsJson(Request $request): array
    {
        $parsed = $request->getParsedBody();
        if (is_array($parsed)) {
            return $parsed;
        }
        $brut = (string) $request->getBody();
        if ($brut === '') {
            return [];
        }
        $donnees = json_decode($brut, true);
        if (!is_array($donnees)) {
            throw new EntreeInvalide('Corps JSON invalide');
        }
        return $donnees;
    }

    /** @param array<string, mixed> $corps */
    private static function chaineRequise(array $corps, string $champ): string
    {
        if (!isset($corps[$champ]) || !is_string($corps[$champ]) || trim($corps[$champ]) === '') {
            throw new EntreeInvalide("Champ « {$champ} » requis (chaîne non vide)");
        }
        return trim($corps[$champ]);
    }

    /** @param array<string, mixed> $corps */
    private static function entierRequis(array $corps, string $champ): int
    {
        if (!isset($corps[$champ]) || !is_int($corps[$champ])) {
            if (isset($corps[$champ]) && is_string($corps[$champ]) && ctype_digit($corps[$champ])) {
                return (int) $corps[$champ];
            }
            throw new EntreeInvalide("Champ « {$champ} » requis (entier)");
        }
        return $corps[$champ];
    }

    /** @param array<string, mixed> $corps */
    private static function dateRequise(array $corps, string $champ): \DateTimeImmutable
    {
        $valeur = self::chaineRequise($corps, $champ);
        try {
            return new \DateTimeImmutable($valeur);
        } catch (\Exception) {
            throw new EntreeInvalide("Champ « {$champ} » doit être une date-heure ISO-8601");
        }
    }

    /** @param array<string, mixed> $payload */
    private static function json(Response $response, array $payload): Response
    {
        $response->getBody()->write(json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
