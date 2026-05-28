<?php

declare(strict_types=1);

namespace SmartAssert\WorkerManagerClient;

use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;
use SmartAssert\ServiceClient\Response\JsonResponse;
use SmartAssert\WorkerManagerClient\Exception\CreateMachineException;
use SmartAssert\WorkerManagerClient\Factory\MachineFactory;
use SmartAssert\WorkerManagerClient\Model\Machine;

readonly class Client
{
    public function __construct(
        private ServiceClient $serviceClient,
        private RequestFactory $requestFactory,
        private MachineFactory $machineFactory,
    ) {}

    /**
     * @param non-empty-string $userToken
     * @param non-empty-string $machineId
     *
     * @throws ClientExceptionInterface
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     * @throws InvalidModelDataException
     * @throws CreateMachineException
     * @throws InvalidResponseTypeException
     * @throws UnauthorizedException
     */
    public function createMachine(string $userToken, string $machineId): Machine
    {
        try {
            $response = $this->serviceClient->sendRequestForJson(
                $this->requestFactory->createMachineRequest($userToken, 'POST', $machineId)
            );
        } catch (NonSuccessResponseException $e) {
            $response = $e->getResponse();

            if (400 === $e->getStatusCode() && $response instanceof JsonResponse) {
                $responseData = $response->getData();
                $message = $responseData['message'] ?? '';
                $message = is_string($message) ? $message : '';

                $code = $responseData['code'] ?? 0;
                $code = is_int($code) ? $code : 0;

                throw new CreateMachineException($message, $code);
            }

            throw $e;
        }

        $machine = $this->machineFactory->create($response->getData());
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
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     * @throws InvalidModelDataException
     * @throws InvalidResponseTypeException
     * @throws UnauthorizedException
     */
    public function getMachine(string $userToken, string $machineId): Machine
    {
        $response = $this->serviceClient->sendRequestForJson(
            $this->requestFactory->createMachineRequest($userToken, 'GET', $machineId)
        );

        $machine = $this->machineFactory->create($response->getData());
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
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     * @throws InvalidModelDataException
     * @throws InvalidResponseTypeException
     * @throws UnauthorizedException
     */
    public function deleteMachine(string $userToken, string $machineId): Machine
    {
        $response = $this->serviceClient->sendRequestForJson(
            $this->requestFactory->createMachineRequest($userToken, 'DELETE', $machineId)
        );

        $machine = $this->machineFactory->create($response->getData());
        if (null === $machine) {
            throw InvalidModelDataException::fromJsonResponse(Machine::class, $response);
        }

        return $machine;
    }
}
