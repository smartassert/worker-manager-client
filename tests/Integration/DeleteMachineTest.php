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
        self::assertSame($machineId, $machine->id);
        self::assertSame($expectedStartState, $machine->state);

        $machine = $this->waitUntilMachineStateIs($expectedEndState, $machine);

        self::assertSame($expectedEndState, $machine->state);
    }
}
