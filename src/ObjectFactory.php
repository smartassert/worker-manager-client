<?php

declare(strict_types=1);

namespace SmartAssert\WorkerManagerClient;

use SmartAssert\WorkerManagerClient\Model\BadCreateMachineResponse;
use SmartAssert\WorkerManagerClient\Model\MachineRequestResponse;

class ObjectFactory
{
    /**
     * @param array<mixed> $data
     */
    public function createMachineRequestResponseFromArray(array $data): ?MachineRequestResponse
    {
        $machineId = $this->getNonEmptyStringValue('machine_id', $data);
        $requestedAction = $this->getNonEmptyStringValue('requested_action', $data);
        $statusUrl = $this->getNonEmptyStringValue('status_url', $data);

        return null === $machineId || null === $requestedAction || null === $statusUrl
            ? null
            : new MachineRequestResponse($machineId, $requestedAction, $statusUrl);
    }

    /**
     * @param array<mixed> $data
     */
    public function createBadCreateMachineResponseFromArray(array $data): ?BadCreateMachineResponse
    {
        $message = $this->getNonEmptyStringValue('message', $data);
        $code = $this->getIntegerValue('code', $data);

        return null === $message || null === $code
            ? null
            : new BadCreateMachineResponse($message, $code);
    }

    /**
     * @param non-empty-string $key
     * @param array<mixed>     $data
     */
    private function getStringValue(string $key, array $data): ?string
    {
        $value = $data[$key] ?? null;

        return is_string($value) ? $value : null;
    }

    /**
     * @param non-empty-string $key
     * @param array<mixed>     $data
     *
     * @return null|non-empty-string
     */
    private function getNonEmptyStringValue(string $key, array $data): ?string
    {
        $value = trim((string) $this->getStringValue($key, $data));

        return '' === $value ? null : $value;
    }

    /**
     * @param non-empty-string $key
     * @param array<mixed>     $data
     */
    private function getIntegerValue(string $key, array $data): ?int
    {
        $value = $data[$key] ?? null;

        return is_int($value) ? $value : null;
    }
}
