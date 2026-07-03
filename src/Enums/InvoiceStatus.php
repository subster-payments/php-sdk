<?php

declare(strict_types=1);

namespace Subster\PhpSdk\Enums;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Open = 'open';
    case Paid = 'paid';
}
