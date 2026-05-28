<?php

declare(strict_types=1);

namespace SmartAssert\WorkerManagerClient\Model;

readonly class MetaState
{
    public function __construct(
        public bool $ended,
        public bool $succeeded,
        public bool $pending,
    ) {}

    public function hasFailedState(): bool
    {
        return $this->ended && !$this->succeeded;
    }

    public function hasEndState(): bool
    {
        return $this->ended;
    }
}
