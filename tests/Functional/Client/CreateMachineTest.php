<?php

declare(strict_types=1);

namespace SmartAssert\WorkerManagerClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;
use SmartAssert\WorkerManagerClient\Model\Machine;

class CreateMachineTest extends AbstractClientTestCase
{
    public function testCreateMachineRequestProperties(): void
    {
        $userToken = md5((string) rand());
        $machineId = md5((string) rand());

        $this->mockHandler->append(new Response(
            202,
            ['content-type' => 'application/json'],
            (string) json_encode([
                'id' => $machineId,
                'state' => 'create/requested',
                'state_category' => 'pre_active',
                'ip_addresses' => [],
                'has_failed_state' => false,
                'has_active_state' => false,
                'has_ending_state' => false,
                'has_end_state' => false,
            ])
        ));

        $this->client->createMachine($userToken, $machineId);

        $request = $this->getLastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('Bearer ' . $userToken, $request->getHeaderLine('authorization'));
    }

    protected function createClientActionCallable(): callable
    {
        return function () {
            $userToken = md5((string) rand());
            $machineId = md5((string) rand());

            $this->client->createMachine($userToken, $machineId);
        };
    }

    protected function getExpectedModelClass(): string
    {
        return Machine::class;
    }
}
