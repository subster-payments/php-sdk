<?php

declare(strict_types=1);

namespace Subster\PhpSdk\Enums;

enum CheckoutSessionTrialInterval: string
{
    case Hour = 'hour';
    case Day = 'day';
    case Week = 'week';
    case Month = 'month';
    case Year = 'year';
}
