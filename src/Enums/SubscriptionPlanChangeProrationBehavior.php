<?php

declare(strict_types=1);

namespace Subster\PhpSdk\Enums;

enum SubscriptionPlanChangeProrationBehavior: string
{
    case Prorate = 'prorate';
    case None = 'none';
}
