<?php

declare(strict_types=1);

namespace Subster\PhpSdk\DataObjects;

use Subster\PhpSdk\Concerns\Data;

class InvoiceItemData extends Data
{
    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        public readonly string $id,
        public readonly ?string $plan,
        public readonly string $product_name,
        public readonly string $type,
        public readonly float $unit_amount,
        public readonly int $quantity,
        public readonly float $amount,
        public readonly ?string $description,
        public readonly ?array $metadata,
    ) {}

    public static function fromSaloon(array $response): self
    {
        return new self(
            id: strval($response['id']),
            plan: isset($response['plan']) ? strval($response['plan']) : null,
            product_name: strval($response['product_name']),
            type: strval($response['type']),
            unit_amount: floatval($response['unit_amount']),
            quantity: (int) $response['quantity'],
            amount: floatval($response['amount']),
            description: isset($response['description']) ? strval($response['description']) : null,
            metadata: isset($response['metadata']) && is_array($response['metadata']) ? $response['metadata'] : null,
        );
    }
}
