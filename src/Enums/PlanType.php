<?php

declare(strict_types=1);

namespace Subster\PhpSdk\Enums;

enum PlanType: string
{
    case Recurring = 'recurring';
    case OneTime = 'one_time';
}
