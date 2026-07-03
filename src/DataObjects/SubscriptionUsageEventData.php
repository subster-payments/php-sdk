<?php

declare(strict_types=1);

namespace Subster\PhpSdk\DataObjects;

use DateTimeImmutable;
use Subster\PhpSdk\Concerns\Data;
use Subster\PhpSdk\Support\DateTimeNormalizer;

class SubscriptionUsageEventData extends Data
{
    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        public readonly string $id,
        public readonly string $subscription,
        public readonly string $customer,
        public readonly ?string $plan,
        public readonly int $quantity,
        public readonly DateTimeImmutable $occurred_at,
        public readonly ?string $idempotency_key,
        public readonly ?array $metadata,
    ) {}

    public static function fromSaloon(array $response): self
    {
        return new self(
            id: strval($response['id']),
            subscription: strval($response['subscription']),
            customer: strval($response['customer']),
            plan: isset($response['plan']) ? strval($response['plan']) : null,
            quantity: (int) $response['quantity'],
            occurred_at: DateTimeNormalizer::parse($response['occurred_at']),
            idempotency_key: isset($response['idempotency_key']) ? strval($response['idempotency_key']) : null,
            metadata: isset($response['metadata']) && is_array($response['metadata']) ? $response['metadata'] : null,
        );
    }
}
