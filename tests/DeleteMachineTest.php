<?php

declare(strict_types=1);

namespace SmartAssert\WorkerManagerClient\Tests;

use SmartAssert\WorkerManagerClient\Model\Machine;

class DeleteMachineTest extends AbstractIntegrationTest
{
    public function testDeleteSuccess(): void
    {
        $machineId = md5((string) rand());

        $expectedStartState = 'delete/received';
        $expectedEndState = 'delete/failed';

        $machine = self::$client->deleteMachine(self::$user1ApiToken->token, $machineId);
        self::assertInstanceOf(Machine::class, $machine);
        self::assertSame($machineId, $machine->id);
        self::assertSame($expectedStartState, $machine->state);

        $machine = $this->waitUntilMachineStateIs($expectedEndState, $machine);

        self::assertSame($expectedEndState, $machine->state);
    }
}
