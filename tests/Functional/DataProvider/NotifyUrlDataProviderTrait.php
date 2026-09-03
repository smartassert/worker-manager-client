<?php

declare(strict_types=1);

namespace SmartAssert\WorkerManagerClient\Tests\Functional\DataProvider;

trait NotifyUrlDataProviderTrait
{
    /**
     * @return array<mixed>
     */
    public static function notifyUrlDataProvider(): array
    {
        return [
            'null notify url' => [
                'notifyUrl' => null,
                'expectedRequestPayload' => '',
            ],
            'non-empty notify url' => [
                'notifyUrl' => 'https://example.com/notify',
                'expectedRequestPayload' => http_build_query(['notify_url' => 'https://example.com/notify']),
            ],
        ];
    }
}
