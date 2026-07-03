<?php

declare(strict_types=1);

namespace Subster\PhpSdk\Enums;

enum SubscriptionStatus: string
{
    case Incomplete = 'incomplete';
    case Active = 'active';
    case PastDue = 'past_due';
    case Canceled = 'canceled';
}
