<?php

declare(strict_types=1);

namespace Subster\PhpSdk\DataObjects;

use Subster\PhpSdk\Concerns\Data;

class CreateInvoiceRefundData extends Data
{
    public function __construct(
        public readonly string $reason,
        public readonly string $idempotencyKey,
        public readonly ?float $amount = null,
    ) {}
}
