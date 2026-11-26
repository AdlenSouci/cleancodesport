<?php
namespace App\Infrastructure\Http;

use App\Application\ConflitDeVersion;
use App\Application\ConsulterCours;
use App\Application\CoursIntrouvable;
use App\Application\CreerCours;
use App\Application\EntreeInvalide;
use App\Application\ErreurApplication;
use App\Application\InscrireParticipant;
use App\Application\ListerCours;
use App\Domain\CapaciteInvalide;
use App\Domain\ErreurDomaine;
use App\Domain\ParticipantDejaInscrit;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Exception\HttpNotFoundException;
use Throwable;

// CreerApplicationHttp — fabrique Slim branchée sur les cas d'usage (composition côté infra).
final class CreerApplicationHttp
{
    private const TYPE_BASE = 'https://exemple.fr/problemes/';

    public static function creer(
        CreerCours $creerCours,
        ListerCours $listerCours,
        ConsulterCours $consulterCours,
        InscrireParticipant $inscrireParticipant,
    ): \Slim\App {
        $app = AppFactory::create();
        $app->addBodyParsingMiddleware();
        $app->addRoutingMiddleware();

        RoutesCours::enregistrer($app, $creerCours, $listerCours, $consulterCours, $inscrireParticipant);

        $errorMiddleware = $app->addErrorMiddleware(false, true, true);
        $errorMiddleware->setDefaultErrorHandler(
            function (
                Request $request,
                Throwable $exception,
                bool $displayErrorDetails,
                bool $logErrors,
                bool $logErrorDetails,
            ) use ($app): Response {
                $response = $app->getResponseFactory()->createResponse();
                return self::probleme($request, $response, $exception);
            }
        );

        return $app;
    }

    private static function probleme(Request $request, Response $response, Throwable $exception): Response
    {
        [$status, $type, $title] = match (true) {
            $exception instanceof EntreeInvalide => [400, 'entree-invalide', $exception->getMessage()],
            $exception instanceof CapaciteInvalide => [422, 'capacite-invalide', $exception->getMessage()],
            $exception instanceof ParticipantDejaInscrit => [409, 'participant-deja-inscrit', $exception->getMessage()],
            $exception instanceof ConflitDeVersion => [409, 'conflit-de-version', $exception->getMessage()],
            $exception instanceof CoursIntrouvable => [404, 'cours-introuvable', $exception->getMessage()],
            $exception instanceof HttpNotFoundException => [404, 'introuvable', 'Ressource introuvable'],
            $exception instanceof ErreurDomaine => [400, 'erreur-domaine', $exception->getMessage()],
            $exception instanceof ErreurApplication => [400, 'erreur-application', $exception->getMessage()],
            default => [500, 'erreur-interne', 'Erreur interne'],
        };

        $corps = [
            'type' => self::TYPE_BASE . $type,
            'title' => $title,
            'status' => $status,
            'instance' => (string) $request->getUri()->getPath(),
        ];

        $response->getBody()->write(json_encode($corps, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
        return $response
            ->withStatus($status)
            ->withHeader('Content-Type', 'application/problem+json');
    }
}
