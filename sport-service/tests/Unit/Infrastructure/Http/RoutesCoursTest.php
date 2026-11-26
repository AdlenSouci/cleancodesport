<?php
namespace Tests\Unit\Infrastructure\Http;

use App\Application\ConsulterCours;
use App\Application\CreerCours;
use App\Application\InscrireParticipant;
use App\Application\ListerCours;
use App\Infrastructure\Http\CreerApplicationHttp;
use App\Infrastructure\Memoire\CoursRepositoryEnMemoire;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Slim\Psr7\Factory\ServerRequestFactory;
use Tests\Doublures\GenerateurIdentifiantPrevisible;

// Tests HTTP sans réseau ni Docker : app Slim + repository en mémoire.
final class RoutesCoursTest extends TestCase
{
    private App $app;
    private ServerRequestFactory $requetes;

    protected function setUp(): void
    {
        $repository = new CoursRepositoryEnMemoire();
        $this->app = CreerApplicationHttp::creer(
            new CreerCours($repository, new GenerateurIdentifiantPrevisible()),
            new ListerCours($repository),
            new ConsulterCours($repository),
            new InscrireParticipant($repository),
        );
        $this->requetes = new ServerRequestFactory();
    }

    public function test_get_sante_renvoie_200(): void
    {
        $reponse = $this->appeler('GET', '/sante');

        $this->assertSame(200, $reponse->getStatusCode());
        $this->assertSame(['statut' => 'ok'], $this->json($reponse));
    }

    public function test_post_cours_cree_et_renvoie_201_avec_location(): void
    {
        $reponse = $this->appeler('POST', '/cours', [
            'intitule' => 'Yoga',
            'places' => 10,
            'debut' => '2026-12-01T10:00:00+01:00',
        ]);

        $this->assertSame(201, $reponse->getStatusCode());
        $this->assertSame('/cours/cours-1', $reponse->getHeaderLine('Location'));
        $corps = $this->json($reponse);
        $this->assertSame('cours-1', $corps['id']);
        $this->assertSame('Yoga', $corps['intitule']);
        $this->assertSame(10, $corps['places']);
        $this->assertSame(0, $corps['nombreInscrits']);
        $this->assertSame(10, $corps['placesRestantes']);
    }

    public function test_get_cours_liste_paginee_renvoie_200(): void
    {
        $this->appeler('POST', '/cours', [
            'intitule' => 'Yoga',
            'places' => 10,
            'debut' => '2026-12-01T10:00:00+01:00',
        ]);

        $reponse = $this->appeler('GET', '/cours?limit=20&offset=0');

        $this->assertSame(200, $reponse->getStatusCode());
        $corps = $this->json($reponse);
        $this->assertSame(1, $corps['total']);
        $this->assertSame(20, $corps['limit']);
        $this->assertSame(0, $corps['offset']);
        $this->assertCount(1, $corps['items']);
        $this->assertSame('cours-1', $corps['items'][0]['id']);
    }

    public function test_get_cours_par_id_renvoie_200(): void
    {
        $this->appeler('POST', '/cours', [
            'intitule' => 'Yoga',
            'places' => 10,
            'debut' => '2026-12-01T10:00:00+01:00',
        ]);

        $reponse = $this->appeler('GET', '/cours/cours-1');

        $this->assertSame(200, $reponse->getStatusCode());
        $corps = $this->json($reponse);
        $this->assertSame('cours-1', $corps['id']);
        $this->assertSame('Yoga', $corps['intitule']);
    }

    public function test_post_inscription_renvoie_200_inscrit(): void
    {
        $this->appeler('POST', '/cours', [
            'intitule' => 'Yoga',
            'places' => 10,
            'debut' => '2026-12-01T10:00:00+01:00',
        ]);

        $reponse = $this->appeler('POST', '/cours/cours-1/inscriptions', [
            'participantId' => '11111111-1111-4111-8111-111111111111',
        ]);

        $this->assertSame(200, $reponse->getStatusCode());
        $this->assertSame(['statut' => 'inscrit'], $this->json($reponse));
    }

    /** @param array<string, mixed>|null $corps */
    private function appeler(string $methode, string $chemin, ?array $corps = null): ResponseInterface
    {
        $requete = $this->requetes->createServerRequest($methode, $chemin);
        if ($corps !== null) {
            $requete = $requete
                ->withHeader('Content-Type', 'application/json')
                ->withParsedBody($corps);
        }

        return $this->app->handle($requete);
    }

    /** @return array<string, mixed> */
    private function json(ResponseInterface $reponse): array
    {
        return json_decode((string) $reponse->getBody(), true, 512, JSON_THROW_ON_ERROR);
    }
}
