<?php

declare(strict_types=1);

namespace SmartAssert\WorkerManagerClient\Model;

readonly class Machine implements MachineInterface
{
    /**
     * @param non-empty-string   $id
     * @param non-empty-string   $state
     * @param non-empty-string   $stateCategory
     * @param non-empty-string[] $ipAddresses
     */
    public function __construct(
        private string $id,
        private string $state,
        private string $stateCategory,
        private array $ipAddresses,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getStateCategory(): string
    {
        return $this->stateCategory;
    }

    public function getIpAddresses(): array
    {
        return $this->ipAddresses;
    }
}
