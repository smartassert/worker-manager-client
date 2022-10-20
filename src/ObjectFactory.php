<?php

declare(strict_types=1);

namespace SmartAssert\WorkerManagerClient;

use SmartAssert\WorkerManagerClient\Model\BadCreateMachineResponse;
use SmartAssert\WorkerManagerClient\Model\Machine;

class ObjectFactory
{
    /**
     * @param array<mixed> $data
     */
    public function createMachineFromArray(array $data): ?Machine
    {
        $id = $this->getNonEmptyStringValue('id', $data);
        $state = $this->getNonEmptyStringValue('state', $data);
        $ipAddresses = $this->getNonEmptyStringArrayValue('ip_addresses', $data);

        return null === $id || null === $state ? null : new Machine($id, $state, $ipAddresses);
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
        return $this->createNonEmptyString(trim((string) $this->getStringValue($key, $data)));
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

    /**
     * @return ?non-empty-string
     */
    private function createNonEmptyString(string $value): ?string
    {
        $value = trim($value);

        return '' === $value ? null : $value;
    }

    /**
     * @param array<mixed> $data
     *
     * @return non-empty-string[]
     */
    private function getNonEmptyStringArrayValue(string $key, array $data): array
    {
        $values = [];

        $unfilteredValues = $data[$key] ?? [];
        $unfilteredValues = is_array($unfilteredValues) ? $unfilteredValues : [];

        foreach ($unfilteredValues as $unfilteredValue) {
            $filteredValue = $this->createNonEmptyString($unfilteredValue);

            if (is_string($filteredValue)) {
                $values[] = $filteredValue;
            }
        }

        return $values;
    }
}
