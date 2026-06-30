<?php

declare(strict_types=1);

namespace Subster\PhpSdk\DataObjects;

use Subster\PhpSdk\Concerns\Data;

class CreateCheckoutSessionItemData extends Data
{
    public function __construct(
        public readonly string $plan,
        public readonly ?int $quantity = null,
    ) {}

    public function toArray(): array
    {
        return [
            'plan' => $this->plan,
            ...($this->quantity !== null ? ['quantity' => $this->quantity] : []),
        ];
    }
}
