<?php

declare(strict_types=1);

namespace SmartAssert\WorkerManagerClient\Model;

readonly class MetaState
{
    public function __construct(
        public bool $ended,
        public bool $succeeded,
    ) {}
}
