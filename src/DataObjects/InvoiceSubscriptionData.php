<?php

declare(strict_types=1);

namespace Subster\PhpSdk\DataObjects;

use DateTimeImmutable;
use Subster\PhpSdk\Concerns\Data;
use Subster\PhpSdk\Enums\SubscriptionStatus;
use Subster\PhpSdk\Support\DateTimeNormalizer;

class InvoiceSubscriptionData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly SubscriptionStatus $status,
        public readonly string $plan,
        public readonly int $quantity,
        public readonly DateTimeImmutable $starts_at,
        public readonly DateTimeImmutable $ends_at,
        public readonly bool $cancel_at_period_end,
    ) {}

    public static function fromSaloon(array $response): self
    {
        return new self(
            id: strval($response['id']),
            status: SubscriptionStatus::from(strval($response['status'])),
            plan: strval($response['plan']),
            quantity: (int) $response['quantity'],
            starts_at: DateTimeNormalizer::parse($response['starts_at']),
            ends_at: DateTimeNormalizer::parse($response['ends_at']),
            cancel_at_period_end: boolval($response['cancel_at_period_end']),
        );
    }
}
