<?php

declare(strict_types=1);

namespace SmartAssert\WorkerManagerClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;
use SmartAssert\WorkerManagerClient\Model\Machine;

class DeleteMachineTest extends AbstractClientTestCase
{
    public function testDeleteMachineRequestProperties(): void
    {
        $userToken = md5((string) rand());
        $machineId = md5((string) rand());

        $this->mockHandler->append(new Response(
            202,
            ['content-type' => 'application/json'],
            (string) json_encode([
                'id' => $machineId,
                'state' => 'delete/requested',
                'state_category' => 'ending',
                'ip_addresses' => [],
                'has_failed_state' => false,
            ])
        ));

        $this->client->deleteMachine($userToken, $machineId);

        $request = $this->getLastRequest();
        self::assertSame('DELETE', $request->getMethod());
        self::assertSame('Bearer ' . $userToken, $request->getHeaderLine('authorization'));
    }

    protected function createClientActionCallable(): callable
    {
        return function () {
            $userToken = md5((string) rand());
            $machineId = md5((string) rand());

            $this->client->deleteMachine($userToken, $machineId);
        };
    }

    protected function getExpectedModelClass(): string
    {
        return Machine::class;
    }
}
