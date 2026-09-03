<?php

declare(strict_types=1);

namespace SmartAssert\WorkerManagerClient;

use SmartAssert\ServiceClient\Authentication\BearerAuthentication;
use SmartAssert\ServiceClient\Payload\UrlEncodedPayload;
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
     * @param non-empty-string                $method
     * @param array<non-empty-string, string> $payload
     */
    public function createMachineRequest(
        string $token,
        string $method,
        string $machineId,
        array $payload = [],
    ): Request {
        $this->authenticationMiddleware->setAuthentication(new BearerAuthentication($token));

        $url = rtrim($this->baseUrl, '/') . '/machine/' . $machineId;
        if ([] !== $payload && 'POST' !== $method) {
            $url .= '?' . http_build_query($payload);
        }

        $request = $this->create($method, $url);
        if ([] !== $payload && 'POST' === $method) {
            $request = $request->withPayload(new UrlEncodedPayload($payload));
        }

        return $request;
    }
}
