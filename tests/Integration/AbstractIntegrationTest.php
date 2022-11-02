<?php

declare(strict_types=1);

namespace SmartAssert\WorkerManagerClient\Tests\Integration;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use PHPUnit\Framework\TestCase;
use SmartAssert\ServiceClient\ArrayAccessor;
use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\ServiceClient\ResponseDecoder;
use SmartAssert\UsersClient\Client as UsersClient;
use SmartAssert\UsersClient\Model\ApiKey;
use SmartAssert\UsersClient\Model\Token;
use SmartAssert\UsersClient\Model\User;
use SmartAssert\UsersClient\ObjectFactory as UsersObjectFactory;
use SmartAssert\WorkerManagerClient\Client;
use SmartAssert\WorkerManagerClient\Model\Machine;
use SmartAssert\WorkerManagerClient\ObjectFactory;

abstract class AbstractIntegrationTest extends TestCase
{
    protected const USER1_EMAIL = 'user1@example.com';
    protected const USER1_PASSWORD = 'password';
    protected const USER2_EMAIL = 'user1@example.com';
    protected const USER2_PASSWORD = 'password';

    protected static Client $client;
    protected static Token $user1ApiToken;

    public static function setUpBeforeClass(): void
    {
        self::$client = new Client(
            'http://localhost:9081',
            self::createServiceClient(),
            new ObjectFactory(
                new ArrayAccessor()
            ),
            new ResponseDecoder(),
        );
        self::$user1ApiToken = self::createUserApiToken(self::USER1_EMAIL, self::USER1_PASSWORD);
    }

    protected static function createUserApiToken(string $email, string $password): Token
    {
        $usersClient = new UsersClient('http://localhost:9080', self::createServiceClient(), new UsersObjectFactory());

        $frontendToken = $usersClient->createFrontendToken($email, $password);
        \assert($frontendToken instanceof Token);

        $frontendTokenUser = $usersClient->verifyFrontendToken($frontendToken);
        \assert($frontendTokenUser instanceof User);

        $apiKeys = $usersClient->listUserApiKeys($frontendToken);
        $defaultApiKey = $apiKeys->getDefault();
        \assert($defaultApiKey instanceof ApiKey);

        $apiToken = $usersClient->createApiToken($defaultApiKey->key);
        \assert($apiToken instanceof Token);

        $apiTokenUser = $usersClient->verifyApiToken($apiToken);
        \assert($apiTokenUser instanceof User);

        return $apiToken;
    }

    protected function waitUntilMachineStateIs(string $expectedState, Machine $machine): Machine
    {
        $waitTotal = 0;
        $waitThreshold = 120;

        while ($expectedState !== $machine->state && $waitTotal < $waitThreshold) {
            $waitTotal += 5;
            sleep(5);
            $machine = self::$client->getMachine(self::$user1ApiToken->token, $machine->id);
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

        return new ServiceClient($httpFactory, $httpFactory, new HttpClient(), new ResponseDecoder());
    }
}
