<?php

declare(strict_types=1);

namespace SmartAssert\WorkerManagerClient\Tests\Integration;

use SmartAssert\WorkerManagerClient\Model\ActionFailure;
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
        self::assertSame($machineId, $machine->id);
        self::assertSame($expectedStartState, $machine->state);

        $machine = $this->waitUntilMachineStateIs($expectedEndState, $machine);
        self::assertSame($expectedEndState, $machine->state);

        self::assertEquals(
            new ActionFailure(
                'find',
                3,
                'api authentication failure',
                []
            ),
            $machine->actionFailure
        );
    }
}
