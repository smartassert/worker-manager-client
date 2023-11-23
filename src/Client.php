<?php

declare(strict_types=1);

namespace SmartAssert\WorkerManagerClient;

use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ArrayInspector\ArrayInspector;
use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;
use SmartAssert\ServiceClient\Response\JsonResponse;
use SmartAssert\WorkerManagerClient\Exception\CreateMachineException;
use SmartAssert\WorkerManagerClient\Model\Machine;

readonly class Client
{
    public function __construct(
        private ServiceClient $serviceClient,
        private RequestFactory $requestFactory,
    ) {
    }

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
                $responseDataInspector = new ArrayInspector($response->getData());

                $message = $responseDataInspector->getString('message');
                $message = is_string($message) ? $message : '';

                $code = $responseDataInspector->getInteger('code');
                $code = is_int($code) ? $code : 0;

                throw new CreateMachineException($message, $code);
            }

            throw $e;
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
        $stateCategory = $data->getNonEmptyString('state_category');

        if (null === $id || null === $state || null === $stateCategory) {
            return null;
        }

        $ipAddresses = $data->getNonEmptyStringArray('ip_addresses');

        return new Machine($id, $state, $stateCategory, $ipAddresses);
    }
}
