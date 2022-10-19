<?php

declare(strict_types=1);

namespace SmartAssert\WorkerManagerClient\Model;

class MachineRequestResponse
{
    public function __construct(
        public readonly string $machineId,
        public readonly string $action,
        public readonly string $statusUrl,
    ) {
    }
}
