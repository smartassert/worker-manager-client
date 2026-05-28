<?php

declare(strict_types=1);

namespace SmartAssert\WorkerManagerClient\Factory;

use SmartAssert\WorkerManagerClient\Model\Machine;
use SmartAssert\WorkerManagerClient\Model\MetaState;

readonly class MachineFactory
{
    public function __construct(
        private ActionFailureFactory $actionFailureFactory,
    ) {}

    /**
     * @param array<mixed> $data
     */
    public function create(array $data): ?Machine
    {
        $id = $data['id'] ?? null;
        $id = is_string($id) ? $id : null;
        $id = '' === $id ? null : $id;
        if (null === $id) {
            return null;
        }

        $state = $data['state'] ?? null;
        $state = is_string($state) ? $state : null;
        $state = '' === $state ? null : $state;
        if (null === $state) {
            return null;
        }

        $stateCategory = $data['state_category'] ?? null;
        $stateCategory = is_string($stateCategory) ? $stateCategory : null;
        $stateCategory = '' === $stateCategory ? null : $stateCategory;
        if (null === $stateCategory) {
            return null;
        }

        $hasActiveState = $data['has_active_state'] ?? null;
        $hasActiveState = is_bool($hasActiveState) ? $hasActiveState : null;
        if (null === $hasActiveState) {
            return null;
        }

        $hasEndingState = $data['has_ending_state'] ?? null;
        $hasEndingState = is_bool($hasEndingState) ? $hasEndingState : null;
        if (null === $hasEndingState) {
            return null;
        }

        $ipAddresses = $data['ip_addresses'] ?? [];
        $ipAddresses = is_array($ipAddresses) ? $ipAddresses : [];

        $filteredIpAddresses = [];
        foreach ($ipAddresses as $ipAddress) {
            if (is_string($ipAddress) && '' !== $ipAddress) {
                $filteredIpAddresses[] = $ipAddress;
            }
        }

        $actionFailure = null;

        $actionFailureData = $data['action_failure'] ?? null;
        $actionFailureData = is_array($actionFailureData) ? $actionFailureData : null;
        if (is_array($actionFailureData)) {
            $actionFailure = $this->actionFailureFactory->create($actionFailureData);

            if (null === $actionFailure) {
                return null;
            }
        }

        $metaState = $data['meta_state'] ?? [];
        $metaState = is_array($metaState) ? $metaState : [];

        $metaStateEnded = $metaState['ended'] ?? false;
        $metaStateEnded = is_bool($metaStateEnded) ? $metaStateEnded : false;

        $metaStateSucceeded = $metaState['succeeded'] ?? false;
        $metaStateSucceeded = is_bool($metaStateSucceeded) ? $metaStateSucceeded : false;

        $metaStatePending = $metaState['pending'] ?? true;
        $metaStatePending = is_bool($metaStatePending) ? $metaStatePending : true;

        return new Machine(
            $id,
            $state,
            $stateCategory,
            $filteredIpAddresses,
            $actionFailure,
            $metaStateEnded && !$metaStateSucceeded,
            $hasActiveState,
            $hasEndingState,
            $metaStateEnded,
            new MetaState(
                $metaStateEnded,
                $metaStateSucceeded,
                $metaStatePending,
            ),
        );
    }
}
