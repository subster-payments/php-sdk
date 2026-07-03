<?php

declare(strict_types=1);

namespace Subster\PhpSdk\DataObjects;

use Subster\PhpSdk\Concerns\Data;
use Subster\PhpSdk\Enums\SubscriptionPlanChangeProrationBehavior;

class ChangeSubscriptionPlanData extends Data
{
    public function __construct(
        public readonly string $plan,
        public readonly ?string $success_url = null,
        public readonly ?string $cancel_url = null,
        public readonly ?SubscriptionPlanChangeProrationBehavior $proration_behavior = null,
    ) {}
}
