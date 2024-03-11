<?php

declare(strict_types=1);

namespace SmartAssert\WorkerManagerClient\Tests\Integration;

use SmartAssert\WorkerManagerClient\Model\Machine;

class DeleteMachineTest extends AbstractIntegrationTestCase
{
    public function testDeleteSuccess(): void
    {
        $machineId = md5((string) rand());

        $expectedStartState = 'delete/received';
        $expectedEndState = 'delete/failed';

        $machine = self::$client->deleteMachine(self::$user1ApiToken, $machineId);
        self::assertInstanceOf(Machine::class, $machine);
        self::assertSame($machineId, $machine->getId());
        self::assertSame($expectedStartState, $machine->getState());

        $machine = $this->waitUntilMachineStateIs($expectedEndState, $machine);

        self::assertSame($expectedEndState, $machine->getState());
    }
}
