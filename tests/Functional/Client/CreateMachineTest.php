<?php

declare(strict_types=1);

namespace SmartAssert\WorkerManagerClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use SmartAssert\WorkerManagerClient\Model\Machine;

class CreateMachineTest extends AbstractClientTestCase
{
    public function testCreateMachineRequestProperties(): void
    {
        $userToken = md5((string) rand());
        $machineId = md5((string) rand());
        $this->setMockCreateResponse($machineId);

        $this->client->createMachine($userToken, $machineId);

        $request = $this->getLastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('Bearer ' . $userToken, $request->getHeaderLine('authorization'));
    }

    /**
     * @param ?non-empty-string $notifyUrl
     */
    #[DataProvider('createMachineNotifyUrlProvider')]
    public function testCreateMachineNotifyUrl(?string $notifyUrl, string $expectedRequestBody): void
    {
        $userToken = md5((string) rand());
        $machineId = md5((string) rand());
        $this->setMockCreateResponse($machineId);

        $this->client->createMachine($userToken, $machineId, $notifyUrl);

        $request = $this->getLastRequest();
        self::assertSame($expectedRequestBody, $request->getBody()->getContents());
    }

    /**
     * @return array<mixed>
     */
    public static function createMachineNotifyUrlProvider(): array
    {
        return [
            'null notify url' => [
                'notifyUrl' => null,
                'expectedRequestBody' => '',
            ],
            'non-empty notify url' => [
                'notifyUrl' => 'https://example.com/notify',
                'expectedRequestBody' => http_build_query(['notify_url' => 'https://example.com/notify']),
            ],
        ];
    }

    protected function createClientActionCallable(): callable
    {
        return function () {
            $userToken = md5((string) rand());
            $machineId = md5((string) rand());

            $this->client->createMachine($userToken, $machineId);
        };
    }

    protected function getExpectedModelClass(): string
    {
        return Machine::class;
    }

    private function setMockCreateResponse(string $machineId): void
    {
        $this->mockHandler->append(new Response(
            202,
            ['content-type' => 'application/json'],
            (string) json_encode([
                'id' => $machineId,
                'state' => 'create/requested',
                'state_category' => 'pre_active',
                'ip_addresses' => [],
                'has_active_state' => false,
                'has_ending_state' => false,
                'meta_state' => [
                    'pending' => true,
                    'ended' => false,
                    'succeeded' => false,
                ],
            ])
        ));
    }
}
