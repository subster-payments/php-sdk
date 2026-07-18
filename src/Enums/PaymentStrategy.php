<?php

declare(strict_types=1);

namespace Subster\PhpSdk\Enums;

enum PaymentStrategy: string
{
    case HostedCheckout = 'hosted_checkout';
    case DefaultThenCheckout = 'default_then_checkout';
}
