<?php

declare(strict_types=1);

namespace Subster\PhpSdk\Enums;

enum InvoiceRefundStatus: string
{
    case None = 'none';
    case Partial = 'partial';
    case Full = 'full';
}
