<?php

declare(strict_types=1);

namespace SmartAssert\WorkerManagerClient;

use SmartAssert\ServiceClient\Authentication\BearerAuthentication;
use SmartAssert\ServiceClient\Request;
use SmartAssert\ServiceClient\RequestFactory\AuthenticationMiddleware;
use SmartAssert\ServiceClient\RequestFactory\RequestFactory as ServiceClientRequestFactory;
use SmartAssert\ServiceClient\RequestFactory\RequestMiddlewareCollection;

class RequestFactory extends ServiceClientRequestFactory
{
    private readonly AuthenticationMiddleware $authenticationMiddleware;

    public function __construct(private readonly string $baseUrl)
    {
        $this->authenticationMiddleware = new AuthenticationMiddleware();

        parent::__construct(
            (new RequestMiddlewareCollection())->set('authentication', $this->authenticationMiddleware)
        );
    }

    /**
     * @param non-empty-string $method
     */
    public function createMachineRequest(string $token, string $method, string $machineId): Request
    {
        $this->authenticationMiddleware->setAuthentication(new BearerAuthentication($token));

        return $this->create($method, rtrim($this->baseUrl, '/') . '/machine/' . $machineId);
    }
}
