<?php

declare(strict_types=1);

namespace Subster\PhpSdk\Enums;

enum SubscriptionPlanChangeMode: string
{
    case Checkout = 'checkout';
    case Scheduled = 'scheduled';
}
