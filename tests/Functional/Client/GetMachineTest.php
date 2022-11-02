<?php

declare(strict_types=1);

namespace SmartAssert\WorkerManagerClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;

class GetMachineTest extends AbstractClientTest
{
    public function testGetMachineRequestProperties(): void
    {
        $userToken = md5((string) rand());
        $machineId = md5((string) rand());

        $this->mockHandler->append(new Response(
            200,
            ['content-type' => 'application/json'],
            (string) json_encode([
                'id' => $machineId,
                'state' => 'create/requested',
                'ip_addresses' => [],
            ])
        ));

        $this->client->getMachine($userToken, $machineId);

        $request = $this->getLastRequest();
        self::assertSame('GET', $request->getMethod());
        self::assertSame('Bearer ' . $userToken, $request->getHeaderLine('authorization'));
    }

    protected function createClientActionCallable(): callable
    {
        return function () {
            $userToken = md5((string) rand());
            $machineId = md5((string) rand());

            $this->client->getMachine($userToken, $machineId);
        };
    }
}
