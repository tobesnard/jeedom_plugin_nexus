<?php

declare(strict_types=1);

use Nexus\Energy\Electricity\Consumption;
use Nexus\Energy\Electricity\Contract;
use Nexus\Energy\Electricity\Service\KwhReading\IKwhReading;

require_once __DIR__ . '/../../vendor/autoload.php';

final class ConsumptionTest extends \PHPUnit\Framework\TestCase
{
    public function testBillingSummaryCalculations(): void
    {
        // Fake readings : 3 days
        $fake = new class () implements IKwhReading {
            public function getDailyReadings(\DateTimeImmutable $start, \DateTimeImmutable $end): array
            {
                return [
                    ['date' => new \DateTimeImmutable('2025-12-01'), 'value' => 10.0],
                    ['date' => new \DateTimeImmutable('2025-12-02'), 'value' => 20.0],
                    ['date' => new \DateTimeImmutable('2025-12-03'), 'value' => 30.0],
                ];
            }

            public function getTotalKwh(\DateTimeImmutable $start, \DateTimeImmutable $end): float
            {
                return 60.0;
            }
        };

        // One contract covering the period
        $contract = new Contract(new \DateTimeImmutable('2025-01-01'), 0.10, 5.0, 'A');

        $consumption = new Consumption($fake, [$contract]);

        $summary = $consumption->getBillingSummary(new \DateTimeImmutable('2025-12-01'), new \DateTimeImmutable('2025-12-03'));

        $this->assertArrayHasKey('totals', $summary);
        $this->assertEquals(60.0, $summary['totals']['kwh']);

        // Vérifie le coût total approximatif : consommation * price + abonnement quotidien * days
        $expectedVariable = 60.0 * 0.10; // 6.0
        $expectedSub = (5.0 / 30.5) * 3; // approx 0.4918
        $expectedTotal = $expectedVariable + $expectedSub;

        $this->assertEqualsWithDelta($expectedTotal, $summary['totals']['cost'], 0.01);
    }
}
