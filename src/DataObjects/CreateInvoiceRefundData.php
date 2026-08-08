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

    public static function from(array $data = []): static
    {
        if ( ! array_key_exists('idempotencyKey', $data) && array_key_exists('idempotency_key', $data)) {
            $data['idempotencyKey'] = $data['idempotency_key'];
        }

        return parent::from($data);
    }
}
