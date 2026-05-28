<?php

declare(strict_types=1);

namespace SmartAssert\WorkerManagerClient\Factory;

use SmartAssert\WorkerManagerClient\Model\ActionFailure;

readonly class ActionFailureFactory
{
    /**
     * @param array<mixed> $data
     */
    public function create(array $data): ?ActionFailure
    {
        $action = $data['action'] ?? null;
        $action = is_string($action) ? trim($action) : null;
        $action = '' === $action ? null : $action;

        $type = $data['type'] ?? null;
        $type = is_string($type) ? trim($type) : null;
        $type = '' === $type ? null : $type;

        if (null === $action || null === $type) {
            return null;
        }

        $context = $data['context'] ?? null;
        $context = is_array($context) ? $context : [];

        $filteredContext = [];
        foreach ($context as $key => $value) {
            if (is_string($key) && (is_int($value) || is_string($value) || null === $value)) {
                $filteredContext[$key] = $value;
            }
        }

        return new ActionFailure($action, $type, $filteredContext);
    }
}
