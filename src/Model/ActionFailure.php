<?php

declare(strict_types=1);

namespace SmartAssert\WorkerManagerClient\Model;

readonly class ActionFailure
{
    /**
     * @param array<string, int|string> $context
     */
    public function __construct(
        public string $action,
        public int $code,
        public string $reason,
        public array $context,
    ) {
    }
}
