<?php

declare(strict_types=1);

namespace Subster\PhpSdk\Enums;

enum CouponDiscountType: string
{
    case Percentage = 'percentage';
    case FixedAmount = 'fixed_amount';
}
