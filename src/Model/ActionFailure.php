<?php

declare(strict_types=1);

namespace SmartAssert\WorkerManagerClient\Model;

readonly class ActionFailure
{
    /**
     * @param non-empty-string               $action
     * @param non-empty-string               $type
     * @param array<string, null|int|string> $context
     */
    public function __construct(
        public string $action,
        public string $type,
        public array $context,
    ) {
    }
}
