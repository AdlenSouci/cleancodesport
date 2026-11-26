<?php
namespace Tests\Unit\Infrastructure\Http;

use App\Application\ConsulterCours;
use App\Application\ConsulterInscription;
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
            new ConsulterInscription($repository),
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

    private const ALICE = '11111111-1111-4111-8111-111111111111';
    private const BOB = '22222222-2222-4222-8222-222222222222';

    private function unCoursDe(int $places): void
    {
        $this->appeler('POST', '/cours', [
            'intitule' => 'Yoga',
            'places' => $places,
            'debut' => '2026-12-01T10:00:00+01:00',
        ]);
    }

    public function test_post_inscription_cree_et_renvoie_201_avec_location(): void
    {
        $this->unCoursDe(10);

        $reponse = $this->appeler('POST', '/cours/cours-1/inscriptions', ['participantId' => self::ALICE]);

        $this->assertSame(201, $reponse->getStatusCode());
        $this->assertSame('/cours/cours-1/inscriptions/' . self::ALICE, $reponse->getHeaderLine('Location'));
        $this->assertSame(['participantId' => self::ALICE, 'statut' => 'inscrit'], $this->json($reponse));
    }

    public function test_post_inscription_au_dela_de_la_capacite_renvoie_201_en_liste_attente(): void
    {
        $this->unCoursDe(1);
        $this->appeler('POST', '/cours/cours-1/inscriptions', ['participantId' => self::ALICE]);

        $reponse = $this->appeler('POST', '/cours/cours-1/inscriptions', ['participantId' => self::BOB]);

        $this->assertSame(201, $reponse->getStatusCode());
        $this->assertSame(['participantId' => self::BOB, 'statut' => 'en_liste_attente'], $this->json($reponse));
    }

    public function test_get_inscription_en_liste_attente_donne_la_position(): void
    {
        $this->unCoursDe(1);
        $this->appeler('POST', '/cours/cours-1/inscriptions', ['participantId' => self::ALICE]);
        $this->appeler('POST', '/cours/cours-1/inscriptions', ['participantId' => self::BOB]);

        $reponse = $this->appeler('GET', '/cours/cours-1/inscriptions/' . self::BOB);

        $this->assertSame(200, $reponse->getStatusCode());
        $this->assertSame(
            ['participantId' => self::BOB, 'statut' => 'en_liste_attente', 'position' => 1],
            $this->json($reponse)
        );
    }

    public function test_le_location_de_l_inscription_designe_une_ressource_consultable(): void
    {
        $this->unCoursDe(10);
        $creation = $this->appeler('POST', '/cours/cours-1/inscriptions', ['participantId' => self::ALICE]);

        $reponse = $this->appeler('GET', $creation->getHeaderLine('Location'));

        $this->assertSame(200, $reponse->getStatusCode());
        $this->assertSame(
            ['participantId' => self::ALICE, 'statut' => 'inscrit', 'position' => null],
            $this->json($reponse)
        );
    }

    public function test_get_inscription_d_un_participant_absent_renvoie_404(): void
    {
        $this->unCoursDe(10);

        $reponse = $this->appeler('GET', '/cours/cours-1/inscriptions/' . self::ALICE);

        $this->assertSame(404, $reponse->getStatusCode());
        $this->assertSame('application/problem+json', $reponse->getHeaderLine('Content-Type'));
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
