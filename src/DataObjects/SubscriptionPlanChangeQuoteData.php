<?php

declare(strict_types=1);

namespace Subster\PhpSdk\DataObjects;

use DateTimeImmutable;
use Subster\PhpSdk\Concerns\Data;
use Subster\PhpSdk\Enums\Currency;
use Subster\PhpSdk\Enums\PaymentStrategy;
use Subster\PhpSdk\Enums\SubscriptionPlanChangeMode;
use Subster\PhpSdk\Enums\SubscriptionPlanChangeProrationBehavior;
use Subster\PhpSdk\Support\DateTimeNormalizer;

class SubscriptionPlanChangeQuoteData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $subscription,
        public readonly string $current_plan,
        public readonly string $target_plan,
        public readonly SubscriptionPlanChangeMode $mode,
        public readonly SubscriptionPlanChangeProrationBehavior $proration_behavior,
        public readonly PaymentStrategy $payment_strategy,
        public readonly float $amount_due,
        public readonly float $credit_amount,
        public readonly Currency $currency,
        public readonly float $target_plan_unit_amount,
        public readonly float $target_recurring_amount,
        public readonly int $quantity,
        public readonly DateTimeImmutable $effective_at,
        public readonly DateTimeImmutable $quoted_at,
        public readonly DateTimeImmutable $expires_at,
    ) {}

    /** @param array<string, mixed> $response */
    public static function fromSaloon(array $response): self
    {
        return new self(
            id: strval($response['id']),
            subscription: strval($response['subscription']),
            current_plan: strval($response['current_plan']),
            target_plan: strval($response['target_plan']),
            mode: SubscriptionPlanChangeMode::from(strval($response['mode'])),
            proration_behavior: SubscriptionPlanChangeProrationBehavior::from(strval($response['proration_behavior'])),
            payment_strategy: PaymentStrategy::from(strval($response['payment_strategy'])),
            amount_due: floatval($response['amount_due']),
            credit_amount: floatval($response['credit_amount']),
            currency: Currency::from(strval($response['currency'])),
            target_plan_unit_amount: floatval($response['target_plan_unit_amount']),
            target_recurring_amount: floatval($response['target_recurring_amount']),
            quantity: intval($response['quantity']),
            effective_at: DateTimeNormalizer::parse($response['effective_at']),
            quoted_at: DateTimeNormalizer::parse($response['quoted_at']),
            expires_at: DateTimeNormalizer::parse($response['expires_at']),
        );
    }
}
