<?php

declare(strict_types=1);

namespace SmartAssert\WorkerManagerClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use SmartAssert\WorkerManagerClient\Model\Machine;

class DeleteMachineTest extends AbstractClientTestCase
{
    public function testDeleteMachineRequestProperties(): void
    {
        $userToken = md5((string) rand());
        $machineId = md5((string) rand());
        $this->setMockDeleteResponse($machineId);

        $this->client->deleteMachine($userToken, $machineId);

        $request = $this->getLastRequest();
        self::assertSame('DELETE', $request->getMethod());
        self::assertSame('Bearer ' . $userToken, $request->getHeaderLine('authorization'));
    }

    /**
     * @param ?non-empty-string $notifyUrl
     */
    #[DataProvider('deleteMachineNotifyUrlProvider')]
    public function testDeleteMachineNotifyUrl(?string $notifyUrl, string $expectedRequestQuery): void
    {
        $userToken = md5((string) rand());
        $machineId = md5((string) rand());
        $this->setMockDeleteResponse($machineId);

        $this->client->deleteMachine($userToken, $machineId, $notifyUrl);

        $request = $this->getLastRequest();
        self::assertSame('', $request->getBody()->getContents());
        self::assertSame($expectedRequestQuery, $request->getUri()->getQuery());
    }

    /**
     * @return array<mixed>
     */
    public static function deleteMachineNotifyUrlProvider(): array
    {
        return [
            'null notify url' => [
                'notifyUrl' => null,
                'expectedRequestQuery' => '',
            ],
            'non-empty notify url' => [
                'notifyUrl' => 'https://example.com/notify',
                'expectedRequestQuery' => http_build_query(['notify_url' => 'https://example.com/notify']),
            ],
        ];
    }

    protected function createClientActionCallable(): callable
    {
        return function () {
            $userToken = md5((string) rand());
            $machineId = md5((string) rand());

            $this->client->deleteMachine($userToken, $machineId);
        };
    }

    protected function getExpectedModelClass(): string
    {
        return Machine::class;
    }

    private function setMockDeleteResponse(string $machineId): void
    {
        $this->mockHandler->append(new Response(
            202,
            ['content-type' => 'application/json'],
            (string) json_encode([
                'id' => $machineId,
                'state' => 'delete/requested',
                'state_category' => 'ending',
                'ip_addresses' => [],
                'has_active_state' => false,
                'has_ending_state' => true,
                'meta_state' => [
                    'pending' => false,
                    'ended' => false,
                    'succeeded' => false,
                ],
            ])
        ));
    }
}
