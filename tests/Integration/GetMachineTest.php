<?php

declare(strict_types=1);

namespace SmartAssert\WorkerManagerClient\Tests\Integration;

use SmartAssert\WorkerManagerClient\Model\ActionFailure;

class GetMachineTest extends AbstractIntegrationTestCase
{
    public function testGetSuccess(): void
    {
        $machineId = md5((string) rand());

        $expectedStartState = 'find/received';
        $expectedEndState = 'find/not-findable';

        $machine = self::$client->getMachine(self::$user1ApiToken, $machineId);
        self::assertSame($machineId, $machine->id);
        self::assertSame($expectedStartState, $machine->state);

        $machine = $this->waitUntilMachineStateIs($expectedEndState, $machine);
        self::assertSame($expectedEndState, $machine->state);
        self::assertTrue($machine->hasFailedState);

        self::assertEquals(
            new ActionFailure(
                'find',
                'vendor_authentication_failure',
                [
                    'provider' => null,
                ]
            ),
            $machine->actionFailure
        );
    }
}
