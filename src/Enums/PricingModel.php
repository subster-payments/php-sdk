<?php

declare(strict_types=1);

namespace Subster\PhpSdk\Enums;

enum PricingModel: string
{
    case FlatRate = 'flat_rate';
    case UsageBased = 'usage_based';
}
