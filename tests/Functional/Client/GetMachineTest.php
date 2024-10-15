<?php

declare(strict_types=1);

namespace SmartAssert\WorkerManagerClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use SmartAssert\WorkerManagerClient\Model\ActionFailure;
use SmartAssert\WorkerManagerClient\Model\Machine;
use Symfony\Component\Uid\Ulid;

class GetMachineTest extends AbstractClientTestCase
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
                'state' => 'up/active',
                'state_category' => 'active',
                'ip_addresses' => [],
                'has_failed_state' => false,
            ])
        ));

        $this->client->getMachine($userToken, $machineId);

        $request = $this->getLastRequest();
        self::assertSame('GET', $request->getMethod());
        self::assertSame('Bearer ' . $userToken, $request->getHeaderLine('authorization'));
    }

    /**
     * @param array<mixed> $responseData
     */
    #[DataProvider('getMachineModelDataProvider')]
    public function testGetMachineModel(array $responseData, Machine $expected): void
    {
        $this->mockHandler->append(new Response(
            200,
            ['content-type' => 'application/json'],
            (string) json_encode($responseData)
        ));

        $machine = $this->client->getMachine(md5((string) rand()), md5((string) rand()));

        self::assertEquals($expected, $machine);
    }

    /**
     * @return array<mixed>
     */
    public static function getMachineModelDataProvider(): array
    {
        $machineId = (string) new Ulid();
        \assert('' !== $machineId);

        $state = md5((string) rand()) . '/' . md5((string) rand());
        $stateCategory = md5((string) rand());
        $ipAddresses = [
            md5((string) rand()),
            md5((string) rand()),
        ];

        $action = md5((string) rand());
        $code = rand();
        $type = md5((string) rand());
        $context = [
            md5((string) rand()) => md5((string) rand()),
            md5((string) rand()) => rand(),
            md5((string) rand()) => md5((string) rand()),
        ];

        return [
            'without action failure, without ip addresses' => [
                'responseData' => [
                    'id' => $machineId,
                    'state' => $state,
                    'state_category' => $stateCategory,
                    'ip_addresses' => [],
                    'has_failed_state' => false,
                ],
                'expected' => new Machine($machineId, $state, $stateCategory, [], null, false),
            ],
            'without action failure, with ip addresses' => [
                'responseData' => [
                    'id' => $machineId,
                    'state' => $state,
                    'state_category' => $stateCategory,
                    'ip_addresses' => $ipAddresses,
                    'has_failed_state' => false,
                ],
                'expected' => new Machine($machineId, $state, $stateCategory, $ipAddresses, null, false),
            ],
            'with action failure, without ip addresses' => [
                'responseData' => [
                    'id' => $machineId,
                    'state' => $state,
                    'state_category' => $stateCategory,
                    'ip_addresses' => [],
                    'action_failure' => [
                        'action' => $action,
                        'code' => $code,
                        'type' => $type,
                        'context' => $context,
                    ],
                    'has_failed_state' => false,
                ],
                'expected' => new Machine(
                    $machineId,
                    $state,
                    $stateCategory,
                    [],
                    new ActionFailure($action, $type, $context),
                    false
                ),
            ],
        ];
    }

    protected function createClientActionCallable(): callable
    {
        return function () {
            $userToken = md5((string) rand());
            $machineId = md5((string) rand());

            $this->client->getMachine($userToken, $machineId);
        };
    }

    protected function getExpectedModelClass(): string
    {
        return Machine::class;
    }
}
