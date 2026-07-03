<?php

declare(strict_types=1);

namespace Subster\PhpSdk\DataObjects;

use DateTimeImmutable;
use Subster\PhpSdk\Concerns\Data;
use Subster\PhpSdk\Enums\SubscriptionPlanChangeMode;
use Subster\PhpSdk\Enums\SubscriptionPlanChangeProrationBehavior;
use Subster\PhpSdk\Support\DateTimeNormalizer;

class SubscriptionPlanChangeData extends Data
{
    public function __construct(
        public readonly SubscriptionPlanChangeMode $mode,
        public readonly string $id,
        public readonly ?string $checkout_session,
        public readonly ?string $checkout_url,
        public readonly float $amount_due,
        public readonly float $credit_amount,
        public readonly DateTimeImmutable $effective_at,
        public readonly ?SubscriptionPlanChangeProrationBehavior $proration_behavior = null,
    ) {}

    public static function fromSaloon(array $response): self
    {
        $isLegacyResponse = array_key_exists('change', $response);

        return new self(
            mode: SubscriptionPlanChangeMode::from(strval($response['mode'])),
            id: strval($isLegacyResponse ? $response['change'] : $response['id']),
            checkout_session: isset($response['checkout_session']) || array_key_exists('checkout_session', $response)
                ? ($response['checkout_session'] !== null ? strval($response['checkout_session']) : null)
                : ($isLegacyResponse && isset($response['id']) ? strval($response['id']) : null),
            checkout_url: isset($response['checkout_url']) || array_key_exists('checkout_url', $response)
                ? ($response['checkout_url'] !== null ? strval($response['checkout_url']) : null)
                : ($isLegacyResponse && isset($response['url']) ? strval($response['url']) : null),
            amount_due: floatval($response['amount_due']),
            credit_amount: floatval($response['credit_amount']),
            effective_at: DateTimeNormalizer::parse($response['effective_at']),
            proration_behavior: isset($response['proration_behavior']) ? SubscriptionPlanChangeProrationBehavior::from(strval($response['proration_behavior'])) : null,
        );
    }
}
