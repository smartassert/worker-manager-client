<?php

declare(strict_types=1);

namespace SmartAssert\WorkerManagerClient;

use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ArrayInspector\ArrayInspector;
use SmartAssert\ServiceClient\Authentication\BearerAuthentication;
use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseContentException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\ServiceClient\Request;
use SmartAssert\WorkerManagerClient\Model\BadCreateMachineResponse;
use SmartAssert\WorkerManagerClient\Model\Machine;

class Client
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly ServiceClient $serviceClient,
    ) {
    }

    /**
     * @param non-empty-string $userToken
     * @param non-empty-string $machineId
     *
     * @throws ClientExceptionInterface
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     * @throws InvalidModelDataException
     */
    public function createMachine(
        string $userToken,
        string $machineId
    ): Machine|BadCreateMachineResponse {
        $response = $this->serviceClient->sendRequestForJsonEncodedData(
            (new Request('POST', $this->createUrl('/machine/' . $machineId)))
                ->withAuthentication(new BearerAuthentication($userToken))
        );

        if (!$response->isSuccessful()) {
            $httpResponse = $response->getHttpResponse();

            if (
                400 === $httpResponse->getStatusCode()
                && 'application/json' === $httpResponse->getHeaderLine('content-type')
            ) {
                $responseDataInspector = new ArrayInspector($response->getData());
                $badRequestResponse = $this->createBadCreateMachineResponseModel($responseDataInspector);

                if ($badRequestResponse instanceof BadCreateMachineResponse) {
                    return $badRequestResponse;
                }
            }

            throw new NonSuccessResponseException($httpResponse);
        }

        $responseDataInspector = new ArrayInspector($response->getData());

        $machine = $this->createMachineModel($responseDataInspector);
        if (null === $machine) {
            throw InvalidModelDataException::fromJsonResponse(Machine::class, $response);
        }

        return $machine;
    }

    /**
     * @param non-empty-string $userToken
     * @param non-empty-string $machineId
     *
     * @throws ClientExceptionInterface
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     * @throws InvalidModelDataException
     */
    public function getMachine(string $userToken, string $machineId): Machine
    {
        $response = $this->serviceClient->sendRequestForJsonEncodedData(
            (new Request('GET', $this->createUrl('/machine/' . $machineId)))
                ->withAuthentication(new BearerAuthentication($userToken))
        );

        if (!$response->isSuccessful()) {
            throw new NonSuccessResponseException($response->getHttpResponse());
        }

        $responseDataInspector = new ArrayInspector($response->getData());

        $machine = $this->createMachineModel($responseDataInspector);
        if (null === $machine) {
            throw InvalidModelDataException::fromJsonResponse(Machine::class, $response);
        }

        return $machine;
    }

    /**
     * @param non-empty-string $userToken
     * @param non-empty-string $machineId
     *
     * @throws ClientExceptionInterface
     * @throws InvalidResponseContentException
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     * @throws InvalidModelDataException
     */
    public function deleteMachine(string $userToken, string $machineId): ?Machine
    {
        $response = $this->serviceClient->sendRequestForJsonEncodedData(
            (new Request('DELETE', $this->createUrl('/machine/' . $machineId)))
                ->withAuthentication(new BearerAuthentication($userToken))
        );

        if (!$response->isSuccessful()) {
            throw new NonSuccessResponseException($response->getHttpResponse());
        }

        $responseDataInspector = new ArrayInspector($response->getData());

        $machine = $this->createMachineModel($responseDataInspector);
        if (null === $machine) {
            throw InvalidModelDataException::fromJsonResponse(Machine::class, $response);
        }

        return $machine;
    }

    public function createMachineModel(ArrayInspector $data): ?Machine
    {
        $id = $data->getNonEmptyString('id');
        $state = $data->getNonEmptyString('state');

        $ipAddressesInspector = new ArrayInspector($data->getArray('ip_addresses'));
        $ipAddresses = [];

        $ipAddressesInspector->each(function (int|string $key, mixed $value) use (&$ipAddresses) {
            if (is_string($value)) {
                $value = trim($value);

                if ('' !== $value) {
                    $ipAddresses[] = $value;
                }
            }
        });

        return null === $id || null === $state ? null : new Machine($id, $state, $ipAddresses);
    }

    public function createBadCreateMachineResponseModel(ArrayInspector $data): ?BadCreateMachineResponse
    {
        $message = $data->getNonEmptyString('message');
        $code = $data->getInteger('code');

        return null === $message || null === $code ? null : new BadCreateMachineResponse($message, $code);
    }

    /**
     * @param non-empty-string $path
     *
     * @return non-empty-string
     */
    private function createUrl(string $path): string
    {
        return rtrim($this->baseUrl, '/') . $path;
    }
}
