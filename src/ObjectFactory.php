<?php

declare(strict_types=1);

namespace SmartAssert\WorkerManagerClient;

use SmartAssert\ServiceClient\ArrayAccessor;
use SmartAssert\WorkerManagerClient\Model\BadCreateMachineResponse;
use SmartAssert\WorkerManagerClient\Model\Machine;

class ObjectFactory
{
    public function __construct(
        private readonly ArrayAccessor $arrayAccessor,
    ) {
    }

    /**
     * @param array<mixed> $data
     */
    public function createMachineFromArray(array $data): ?Machine
    {
        $id = $this->arrayAccessor->getNonEmptyString('id', $data);
        $state = $this->arrayAccessor->getNonEmptyString('state', $data);
        $ipAddresses = $this->arrayAccessor->getNonEmptyStringArray('ip_addresses', $data);

        return null === $id || null === $state ? null : new Machine($id, $state, $ipAddresses);
    }

    /**
     * @param array<mixed> $data
     */
    public function createBadCreateMachineResponseFromArray(array $data): ?BadCreateMachineResponse
    {
        $message = $this->arrayAccessor->getNonEmptyString('message', $data);
        $code = $this->arrayAccessor->getInteger('code', $data);

        return null === $message || null === $code
            ? null
            : new BadCreateMachineResponse($message, $code);
    }
}
