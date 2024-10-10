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
use SmartAssert\WorkerManagerClient\Model\ActionFailure;
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
                $responseData = $response->getData();
                $message = $responseData['message'] ?? '';
                $message = is_string($message) ? $message : '';

                $code = $responseData['code'] ?? 0;
                $code = is_int($code) ? $code : 0;

                throw new CreateMachineException($message, $code);
            }

            throw $e;
        }

        $machine = $this->createMachineModel($response->getData());
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

        $machine = $this->createMachineModel($response->getData());
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

        $machine = $this->createMachineModel($response->getData());
        if (null === $machine) {
            throw InvalidModelDataException::fromJsonResponse(Machine::class, $response);
        }

        return $machine;
    }

    /**
     * @param array<mixed> $data
     */
    public function createMachineModel(array $data): ?Machine
    {
        $id = $data['id'] ?? null;
        $id = is_string($id) ? $id : null;
        $id = '' === $id ? null : $id;

        $state = $data['state'] ?? null;
        $state = is_string($state) ? $state : null;
        $state = '' === $state ? null : $state;

        $stateCategory = $data['state_category'] ?? null;
        $stateCategory = is_string($stateCategory) ? $stateCategory : null;
        $stateCategory = '' === $stateCategory ? null : $stateCategory;

        if (null === $id || null === $state || null === $stateCategory) {
            return null;
        }

        $ipAddresses = $data['ip_addresses'] ?? [];
        $ipAddresses = is_array($ipAddresses) ? $ipAddresses : [];

        $filteredIpAddresses = [];
        foreach ($ipAddresses as $ipAddress) {
            if (is_string($ipAddress) && '' !== $ipAddress) {
                $filteredIpAddresses[] = $ipAddress;
            }
        }

        $actionFailure = null;

        $actionFailureData = $data['action_failure'] ?? null;
        $actionFailureData = is_array($actionFailureData) ? $actionFailureData : null;
        if (is_array($actionFailureData)) {
            $actionFailure = $this->createActionFailureModel($actionFailureData);

            if (null === $actionFailure) {
                return null;
            }
        }

        return new Machine($id, $state, $stateCategory, $filteredIpAddresses, $actionFailure);
    }

    /**
     * @param array<mixed> $data
     */
    private function createActionFailureModel(array $data): ?ActionFailure
    {
        $action = $data['action'] ?? null;
        $action = is_string($action) ? trim($action) : null;
        $action = '' === $action ? null : $action;

        $type = $data['type'] ?? null;
        $type = is_string($type) ? trim($type) : null;
        $type = '' === $type ? null : $type;

        if (null === $action || null === $type) {
            return null;
        }

        $context = $data['context'] ?? null;
        $context = is_array($context) ? $context : [];

        $filteredContext = [];
        foreach ($context as $key => $value) {
            if (is_string($key) && (is_int($value) || is_string($value) || null === $value)) {
                $filteredContext[$key] = $value;
            }
        }

        return new ActionFailure($action, $type, $filteredContext);
    }
}
