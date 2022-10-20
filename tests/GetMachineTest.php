<?php

declare(strict_types=1);

namespace SmartAssert\WorkerManagerClient\Tests;

use SmartAssert\WorkerManagerClient\Model\Machine;

class GetMachineTest extends AbstractIntegrationTest
{
    public function testGetSuccess(): void
    {
        $machineId = md5((string) rand());

        $expectedStartState = 'find/received';
        $expectedEndState = 'find/not-findable';

        $machine = self::$client->getMachine(self::$user1ApiToken->token, $machineId);
        self::assertInstanceOf(Machine::class, $machine);
        self::assertSame($machineId, $machine->id);
        self::assertSame($expectedStartState, $machine->state);

        $waitTotal = 0;
        $waitThreshold = 120;

        while ('find/not-findable' !== $machine->state && $waitTotal < $waitThreshold) {
            $waitTotal += 5;
            sleep(5);
            $machine = self::$client->getMachine(self::$user1ApiToken->token, $machineId);
            self::assertInstanceOf(Machine::class, $machine);
        }

        if ($waitTotal >= $waitThreshold) {
            self::fail(sprintf(
                'Waited %s seconds of %s for machine to be "%s". Machine is "%s"',
                $waitTotal,
                $waitThreshold,
                $expectedEndState,
                $machine->state
            ));
        }

        self::assertSame('find/not-findable', $machine->state);
    }
}
