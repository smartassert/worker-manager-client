<?php

declare(strict_types=1);

namespace SmartAssert\WorkerManagerClient\Model;

class BadCreateMachineResponse
{
    public function __construct(
        public readonly string $message,
        public readonly int $code,
    ) {
    }
}
