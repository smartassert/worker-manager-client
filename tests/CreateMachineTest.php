<?php

declare(strict_types=1);

namespace SmartAssert\WorkerManagerClient\Tests;

use SmartAssert\WorkerManagerClient\Model\BadCreateMachineResponse;
use SmartAssert\WorkerManagerClient\Model\Machine;

class CreateMachineTest extends AbstractIntegrationTest
{
    public function testCreateBadRequestIdTaken(): void
    {
        $machineId = md5((string) rand());

        $response = self::$client->createMachine(self::$user1ApiToken->token, $machineId);
        if ($response instanceof Machine) {
            $response = self::$client->createMachine(self::$user1ApiToken->token, $machineId);
        }

        self::assertInstanceOf(BadCreateMachineResponse::class, $response);
        self::assertSame(100, $response->code);
    }

    public function testCreateSuccess(): void
    {
        $machineId = md5((string) rand());

        $response = self::$client->createMachine(self::$user1ApiToken->token, $machineId);
        self::assertInstanceOf(Machine::class, $response);
        self::assertEquals(
            new Machine($machineId, 'create/received', []),
            $response
        );
    }
}
