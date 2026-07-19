<?php

declare(strict_types=1);

namespace Subster\PhpSdk\Enums;

enum CheckoutPaymentAttemptState: string
{
    case NotAttempted = 'not_attempted';
    case Processing = 'processing';
    case Failed = 'failed';
    case Succeeded = 'succeeded';
}
