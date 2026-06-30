<?php

declare(strict_types=1);

namespace Subster\PhpSdk\DataObjects;

use Carbon\Carbon;
use Subster\PhpSdk\Concerns\Data;

class InvoiceSubscriptionData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $status,
        public readonly string $plan,
        public readonly int $quantity,
        public readonly Carbon $starts_at,
        public readonly Carbon $ends_at,
        public readonly bool $cancel_at_period_end,
    ) {}

    public static function fromSaloon(array $response): self
    {
        return new self(
            id: strval($response['id']),
            status: strval($response['status']),
            plan: strval($response['plan']),
            quantity: (int) $response['quantity'],
            starts_at: Carbon::parse($response['starts_at']),
            ends_at: Carbon::parse($response['ends_at']),
            cancel_at_period_end: boolval($response['cancel_at_period_end']),
        );
    }
}
