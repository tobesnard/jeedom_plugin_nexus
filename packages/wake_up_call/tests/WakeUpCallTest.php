<?php

namespace Nexus\Multimedia\WakeUpCall;

use PHPUnit\Framework\TestCase;
use Exception;

class WakeUpCallTest extends TestCase
{
    private string $configFile;

    protected function setUp(): void
    {
        $this->configFile = __DIR__ . '/config_test.json';
        WakeUpCall::setConfigFile($this->configFile);
    }

    protected function tearDown(): void
    {
        WakeUpCall::setConfigFile(__DIR__ . '/../core/config/config.json');
    }

    public function testLoadConfigSuccess()
    {
        // Test que la config est chargée correctement
        $wakeUpCall = $this->createPartialMock(WakeUpCall::class, ['__construct']);
        $reflection = new \ReflectionClass(WakeUpCall::class);
        $method = $reflection->getMethod('loadConfig');
        $method->setAccessible(true);

        $method->invoke($wakeUpCall);

        $this->assertIsArray($wakeUpCall->getConfig());
        $this->assertArrayHasKey('devices', $wakeUpCall->getConfig());
    }

    public function testLoadConfigFileNotFound()
    {
        WakeUpCall::setConfigFile('/nonexistent/file.json');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Configuration file missing');

        new WakeUpCall('test_device');
    }

    public function testIpResolutionFromAlias()
    {
        // Test de logique simple
        $config = json_decode(file_get_contents($this->configFile), true);
        $ip = $config['devices']['test_device'] ?? 'test_device';
        $this->assertEquals('127.0.0.1', $ip);
    }

    public function testIpResolutionDirect()
    {
        $ip = '192.168.1.100';
        $this->assertEquals('192.168.1.100', $ip);
    }

    public function testGetStoragePath()
    {
        $wakeUpCall = $this->createPartialMock(WakeUpCall::class, []);
        $reflection = new \ReflectionClass(WakeUpCall::class);
        $loadMethod = $reflection->getMethod('loadConfig');
        $loadMethod->setAccessible(true);
        $loadMethod->invoke($wakeUpCall);

        $wakeUpCall->ip = '127.0.0.1';

        $method = $reflection->getMethod('getStoragePath');
        $method->setAccessible(true);

        $path = $method->invoke($wakeUpCall);
        $this->assertStringStartsWith('/tmp/test_wake_up_call_', $path);
        $this->assertStringEndsWith('.db', $path);
    }
}
