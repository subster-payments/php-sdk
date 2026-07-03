<?php

declare(strict_types=1);

namespace Subster\PhpSdk\DataObjects;

use Subster\PhpSdk\Concerns\Data;
use Subster\PhpSdk\Enums\CheckoutSessionStatus;
use Subster\PhpSdk\Enums\WebhookEndpointEvent;

class CheckoutSessionStatusData extends Data
{
    /**
     * @param  array<string, mixed>|null  $data
     */
    public function __construct(
        public readonly string $id,
        public readonly CheckoutSessionStatus $status,
        public readonly ?WebhookEndpointEvent $event,
        public readonly ?array $data,
    ) {}

    public static function fromSaloon(array $response): self
    {
        return new self(
            id: strval($response['id']),
            status: CheckoutSessionStatus::from(strval($response['status'])),
            event: isset($response['event']) ? WebhookEndpointEvent::from(strval($response['event'])) : null,
            data: isset($response['data']) && is_array($response['data']) ? $response['data'] : null,
        );
    }
}
