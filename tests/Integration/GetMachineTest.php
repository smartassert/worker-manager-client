<?php

declare(strict_types=1);

namespace SmartAssert\WorkerManagerClient\Tests\Integration;

use SmartAssert\WorkerManagerClient\Model\Machine;

class GetMachineTest extends AbstractIntegrationTestCase
{
    public function testGetSuccess(): void
    {
        $machineId = md5((string) rand());

        $expectedStartState = 'find/received';
        $expectedEndState = 'find/not-findable';

        $machine = self::$client->getMachine(self::$user1ApiToken, $machineId);
        self::assertInstanceOf(Machine::class, $machine);
        self::assertSame($machineId, $machine->getId());
        self::assertSame($expectedStartState, $machine->getState());

        $machine = $this->waitUntilMachineStateIs($expectedEndState, $machine);

        self::assertSame($expectedEndState, $machine->getState());
    }
}
