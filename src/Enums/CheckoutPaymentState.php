<?php

declare(strict_types=1);

namespace Subster\PhpSdk\Enums;

enum CheckoutPaymentState: string
{
    case Paid = 'paid';
    case RequiresPayment = 'requires_payment';
}
