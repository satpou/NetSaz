<?php

namespace Tests\Unit\Services;

use App\Models\GenieacsConfig;
use App\Services\GenieacsService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GenieacsServiceTest extends TestCase
{
    private function makeConfig(array $overrides = []): GenieacsConfig
    {
        return new GenieacsConfig(array_merge([
            'enabled' => true,
            'host' => '192.168.10.5',
            'port' => '7557',
            'nbi_base_url' => null,
        ], $overrides));
    }

    public function test_health_returns_reachable_when_nbi_responds(): void
    {
        Http::fake([
            '*/health' => Http::response(['status' => 'up'], 200),
        ]);

        $service = new GenieacsService($this->makeConfig());

        $health = $service->checkHealth();
        $this->assertTrue($health['reachable']);
    }

    public function test_health_returns_unreachable_on_failure(): void
    {
        Http::fake([
            '*/health' => Http::response([], 500),
        ]);

        $service = new GenieacsService($this->makeConfig());

        $health = $service->checkHealth();
        $this->assertFalse($health['reachable']);
    }

    public function test_get_devices_calls_nbi_devices_endpoint(): void
    {
        Http::fake([
            '*/devices?*' => Http::response([
                ['_id' => 'HWTC-AAAA', '_lastInform' => time(), 'InternetGatewayDevice.DeviceInfo.SoftwareVersion' => 'V1.0'],
            ], 200),
        ]);

        $service = new GenieacsService($this->makeConfig());

        $devices = $service->getDevices();
        $this->assertCount(1, $devices);
        $this->assertSame('HWTC-AAAA', $devices[0]['serial']);
        $this->assertTrue($devices[0]['online']);
    }

    public function test_create_task_posts_to_device_tasks_endpoint(): void
    {
        Http::fake([
            '*/devices/*/tasks' => Http::response([], 200),
        ]);

        $service = new GenieacsService($this->makeConfig());

        $this->assertTrue($service->reboot('HWTC-AAAA'));

        Http::assertSent(fn($request) =>
            $request->url() === 'http://192.168.10.5:7557/devices/HWTC-AAAA/tasks'
            && $request['name'] === 'reboot'
        );
    }

    public function test_set_preseed_uses_put_when_preseed_exists(): void
    {
        Http::fake([
            '*/preseed/HWTC-AAAA' => Http::sequence()
                ->push([], 200)   // GET -> exists
                ->push([], 200),  // PUT
        ]);

        $service = new GenieacsService($this->makeConfig());

        $this->assertTrue($service->setPreseed('HWTC-AAAA', ['Foo' => 'Bar']));

        Http::assertSentCount(2);
        Http::assertSent(fn($request) => $request->method() === 'PUT' && $request->url() === 'http://192.168.10.5:7557/preseed/HWTC-AAAA');
    }

    public function test_provision_writes_preseed_then_reboots(): void
    {
        Http::fake([
            '*/preseed/*' => Http::response([], 200),
            '*/devices/*/tasks' => Http::response([], 200),
        ]);

        $service = new GenieacsService($this->makeConfig());

        $result = $service->provision('HWTC-AAAA', ['InternetGatewayDevice.DeviceInfo.ProvisioningCode' => 'prima']);

        $this->assertTrue($result['success']);
        Http::assertSentCount(3);
    }

    public function test_unconfigured_service_returns_safe_defaults(): void
    {
        $service = new GenieacsService();

        $this->assertFalse($service->isConfigured());
        $this->assertNull($service->checkHealth());
        $this->assertSame([], $service->getDevices());
        $this->assertFalse($service->reboot('HWTC-AAAA'));
        $this->assertFalse($service->setPreseed('HWTC-AAAA', ['a' => 'b']));
    }
}
