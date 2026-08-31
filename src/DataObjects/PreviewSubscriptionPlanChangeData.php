<?php

declare(strict_types=1);

namespace Subster\PhpSdk\DataObjects;

use Subster\PhpSdk\Concerns\Data;
use Subster\PhpSdk\Enums\PaymentStrategy;
use Subster\PhpSdk\Enums\SubscriptionPlanChangeProrationBehavior;

class PreviewSubscriptionPlanChangeData extends Data
{
    public function __construct(
        public readonly string $plan,
        public readonly ?SubscriptionPlanChangeProrationBehavior $proration_behavior = null,
        public readonly ?PaymentStrategy $payment_strategy = null,
    ) {}
}
