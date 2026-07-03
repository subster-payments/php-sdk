<?php

declare(strict_types=1);

namespace Subster\PhpSdk\Enums;

enum CouponDuration: string
{
    case Once = 'once';
    case Repeating = 'repeating';
    case Forever = 'forever';
}
