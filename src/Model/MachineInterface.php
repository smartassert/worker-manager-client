<?php

declare(strict_types=1);

namespace SmartAssert\WorkerManagerClient\Model;

interface MachineInterface
{
    /**
     * @return non-empty-string
     */
    public function getId(): string;

    /**
     * @return non-empty-string
     */
    public function getState(): string;

    /**
     * @return non-empty-string
     */
    public function getStateCategory(): string;

    /**
     * @return non-empty-string[] $ipAddresses
     */
    public function getIpAddresses(): array;
}
