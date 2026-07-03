<?php

declare(strict_types=1);

namespace Subster\PhpSdk\DataObjects;

use Subster\PhpSdk\Concerns\Data;

class InvoiceDiscountPromotionCodeData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $code,
    ) {}

    public static function fromSaloon(array $response): self
    {
        return new self(
            id: strval($response['id']),
            code: strval($response['code']),
        );
    }
}
