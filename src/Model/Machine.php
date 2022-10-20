<?php

declare(strict_types=1);

namespace SmartAssert\WorkerManagerClient\Model;

class Machine
{
    /**
     * @param non-empty-string $id
     * @param string[]         $ipAddresses
     */
    public function __construct(
        public readonly string $id,
        public readonly string $state,
        public readonly array $ipAddresses,
    ) {
    }
}
