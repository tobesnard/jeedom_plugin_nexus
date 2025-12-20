<?php

declare(strict_types=1);

namespace Nexus\Tests\Jeedom;

use PHPUnit\Framework\TestCase;
use Nexus\Jeedom\CmdServiceMock;

/**
 * Test de validation du Mock Service Jeedom
 */
class CmdServiceMockTest extends TestCase
{
    private CmdServiceMock $service;

    protected function setUp(): void
    {
        $this->service = new CmdServiceMock();
    }

    /**
     * Test de l'exécution par chaîne (string)
     * Vérifie les types de retour spécifiques (float, bool)
     */
    public function testExecByStringReturnsCorrectValues(): void
    {
        // Test température (Rule 6)
        $temp = $this->service->execByString('#[edom][edom][temperature]#');
        $this->assertIsFloat($temp);
        $this->assertEquals(18.5, $temp);

        // Test commande On (Rule 9)
        $on = $this->service->execByString('#[edom][cmd][on]#');
        $this->assertTrue($on);

        // Test commande inconnue
        $unknown = $this->service->execByString('#[unknown]#');
        $this->assertFalse($unknown);
    }

    /**
     * Test de l'exécution par ID
     */
    public function testExecByIdReturnsCorrectValues(): void
    {
        // Test ID 18 (température)
        $this->assertEquals(18.5, $this->service->execById(18));

        // Test ID 15137 (string)
        $this->assertEquals('hello from 15137', $this->service->execById(15137));

        // Test ID inconnu
        $this->assertFalse($this->service->execById(999));
    }

    /**
     * Test de la persistence dans l'historique d'exécution
     */
    public function testExecutionHistoryIsLogged(): void
    {
        $this->service->execById(132, ['param' => 'value']);

        $history = $this->service->getExecutionHistory();

        $this->assertCount(1, $history);
        $this->assertEquals('id', $history[0]['type']);
        $this->assertEquals('132', $history[0]['identifier']);
        $this->assertArrayHasKey('param', $history[0]['options']);
    }

    /**
     * Test des événements (Event)
     */
    public function testEventsAreLogged(): void
    {
        $this->service->eventById(456, "nouvelle_valeur");
        $this->service->eventByString("#[ma][cmd]#", true);

        $stats = $this->service->getStats();
        $history = $this->service->getEventHistory();

        $this->assertEquals(2, $stats['events']);
        $this->assertEquals("nouvelle_valeur", $history[0]['value']);
        $this->assertTrue($history[1]['value']);
    }

    /**
     * Test du nettoyage de l'historique
     */
    public function testClearHistoryWorks(): void
    {
        $this->service->execById(18);
        $this->service->eventById(18, 20);

        $this->service->clearHistory();

        $stats = $this->service->getStats();
        $this->assertEquals(0, $stats['total_operations']);
        $this->assertEmpty($this->service->getExecutionHistory());
    }

    /**
     * Test de la méthode de log simple
     */
    public function testLogMethod(): void
    {
        $this->assertTrue($this->service->log("Message de test"));
    }
}
