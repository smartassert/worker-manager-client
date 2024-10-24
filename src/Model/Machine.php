<?php

declare(strict_types=1);

namespace SmartAssert\WorkerManagerClient\Model;

readonly class Machine
{
    /**
     * @param non-empty-string   $id
     * @param non-empty-string   $state
     * @param non-empty-string   $stateCategory
     * @param non-empty-string[] $ipAddresses
     */
    public function __construct(
        public string $id,
        public string $state,
        public string $stateCategory,
        public array $ipAddresses,
        public ?ActionFailure $actionFailure,
        public bool $hasFailedState,
        public bool $hasActiveState,
        public bool $hasEndingState,
        public bool $hasEndState,
    ) {
    }
}
