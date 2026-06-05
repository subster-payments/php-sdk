<?php

declare(strict_types=1);

namespace Subster\PhpSdk\DataObjects;

use Carbon\Carbon;
use Subster\PhpSdk\Concerns\Data;

class SubscriptionPlanChangeData extends Data
{
    public function __construct(
        public readonly string $mode,
        public readonly string $change,
        public readonly ?string $id,
        public readonly ?string $url,
        public readonly float $amount_due,
        public readonly float $credit_amount,
        public readonly Carbon $effective_at,
    ) {}

    public static function fromSaloon(array $response): self
    {
        return new self(
            mode: strval($response['mode']),
            change: strval($response['change']),
            id: isset($response['id']) ? strval($response['id']) : null,
            url: isset($response['url']) ? strval($response['url']) : null,
            amount_due: floatval($response['amount_due']),
            credit_amount: floatval($response['credit_amount']),
            effective_at: Carbon::parse($response['effective_at']),
        );
    }
}
