<?php

declare(strict_types=1);

namespace SmartAssert\WorkerManagerClient\Tests\Integration;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use PHPUnit\Framework\TestCase;
use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\ServiceClient\ExceptionFactory\CurlExceptionFactory;
use SmartAssert\ServiceClient\ResponseFactory\ResponseFactory;
use SmartAssert\TestAuthenticationProviderBundle\ApiKeyProvider;
use SmartAssert\TestAuthenticationProviderBundle\ApiTokenProvider;
use SmartAssert\TestAuthenticationProviderBundle\FrontendTokenProvider;
use SmartAssert\WorkerManagerClient\Client;
use SmartAssert\WorkerManagerClient\Model\Machine;
use SmartAssert\WorkerManagerClient\RequestFactory;

abstract class AbstractIntegrationTestCase extends TestCase
{
    protected const USER1_EMAIL = 'user1@example.com';
    protected const USER1_PASSWORD = 'password';
    protected const USER2_EMAIL = 'user2@example.com';
    protected const USER2_PASSWORD = 'password';

    protected static Client $client;

    /**
     * @var non-empty-string
     */
    protected static string $user1ApiToken;

    public static function setUpBeforeClass(): void
    {
        self::$client = new Client(
            self::createServiceClient(),
            new RequestFactory('http://localhost:9081'),
        );
        self::$user1ApiToken = self::createUserApiToken(self::USER1_EMAIL);
    }

    /**
     * @param non-empty-string $email
     *
     * @return non-empty-string
     */
    protected static function createUserApiToken(string $email): string
    {
        $usersBaseUrl = 'http://localhost:9080';
        $httpClient = new HttpClient();

        $frontendTokenProvider = new FrontendTokenProvider(
            [
                self::USER1_EMAIL => self::USER1_PASSWORD,
                self::USER2_EMAIL => self::USER2_PASSWORD,
            ],
            $usersBaseUrl,
            $httpClient
        );
        $apiKeyProvider = new ApiKeyProvider($usersBaseUrl, $httpClient, $frontendTokenProvider);
        $apiTokenProvider = new ApiTokenProvider($usersBaseUrl, $httpClient, $apiKeyProvider);

        return $apiTokenProvider->get($email);
    }

    protected function waitUntilMachineStateIs(string $expectedState, Machine $machine): Machine
    {
        $waitTotal = 0;
        $waitThreshold = 120;

        while ($expectedState !== $machine->state && $waitTotal < $waitThreshold) {
            $waitTotal += 5;
            sleep(5);
            $machine = self::$client->getMachine(self::$user1ApiToken, $machine->id);
            self::assertInstanceOf(Machine::class, $machine);
        }

        if ($waitTotal >= $waitThreshold) {
            self::fail(sprintf(
                'Waited %s seconds of %s for machine to be "%s". Machine is "%s"',
                $waitTotal,
                $waitThreshold,
                $expectedState,
                $machine->state
            ));
        }

        return $machine;
    }

    private static function createServiceClient(): ServiceClient
    {
        $httpFactory = new HttpFactory();

        return new ServiceClient(
            $httpFactory,
            $httpFactory,
            new HttpClient(),
            ResponseFactory::createFactory(),
            new CurlExceptionFactory(),
        );
    }
}
