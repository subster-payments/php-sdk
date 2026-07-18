<?php

declare(strict_types=1);

namespace Subster\PhpSdk\Enums;

enum BillingPortalFlow: string
{
    case Overview = 'overview';
    case PaymentRecovery = 'payment_recovery';
}
