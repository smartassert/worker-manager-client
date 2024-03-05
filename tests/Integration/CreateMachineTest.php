<?php

declare(strict_types=1);

namespace SmartAssert\WorkerManagerClient\Tests\Integration;

use SmartAssert\WorkerManagerClient\Exception\CreateMachineException;
use SmartAssert\WorkerManagerClient\Model\Machine;

class CreateMachineTest extends AbstractIntegrationTestCase
{
    public function testCreateBadRequestIdTaken(): void
    {
        $machineId = md5((string) rand());

        try {
            self::$client->createMachine(self::$user1ApiToken, $machineId);
            self::$client->createMachine(self::$user1ApiToken, $machineId);
            self::fail(CreateMachineException::class . ' not thrown');
        } catch (CreateMachineException $e) {
            self::assertSame('id taken', $e->getMessage());
            self::assertSame(100, $e->getCode());
        }
    }

    public function testCreateSuccess(): void
    {
        $machineId = md5((string) rand());

        $response = self::$client->createMachine(self::$user1ApiToken, $machineId);
        self::assertInstanceOf(Machine::class, $response);
        self::assertEquals(
            new Machine($machineId, 'create/received', 'pre_active', []),
            $response
        );
    }
}
