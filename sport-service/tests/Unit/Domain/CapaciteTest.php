<?php
namespace Tests\Unit\Domain;
use App\Domain\Capacite;
use App\Domain\CapaciteInvalide;

final class CapaciteTest extends \PHPUnit\Framework\TestCase
{
    public function test_une_capacite_positive_est_acceptee()
    {
        $capacite = new Capacite(20);
        $this->assertSame(20, $capacite->places);
    }
    public function test_une_capacite_nulle_est_refusee()
    {
        $this->expectException(CapaciteInvalide::class);
        new Capacite(0);
    }
    public function test_une_capacite_negative_est_refusee()
    {
        $this->expectException(CapaciteInvalide::class);
        new Capacite(-5);
    }
    public function test_deux_capacites_de_meme_valeur_sont_egales()
    {
        $capacite1 = new Capacite(20);
        $capacite2 = new Capacite(20);
        $capacite3 = new Capacite(15);
        $this->assertTrue($capacite1->egale($capacite2));
        $this->assertFalse($capacite1->egale($capacite3));
    }
    
}