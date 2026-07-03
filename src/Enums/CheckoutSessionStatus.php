<?php

declare(strict_types=1);

namespace Subster\PhpSdk\Enums;

enum CheckoutSessionStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
}
