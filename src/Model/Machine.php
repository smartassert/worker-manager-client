<?php

declare(strict_types=1);

namespace SmartAssert\WorkerManagerClient\Model;

class Machine
{
    /**
     * @param non-empty-string   $id
     * @param non-empty-string   $state
     * @param non-empty-string   $stateCategory
     * @param non-empty-string[] $ipAddresses
     */
    public function __construct(
        public readonly string $id,
        public readonly string $state,
        public readonly string $stateCategory,
        public readonly array $ipAddresses,
    ) {
    }
}
