<?php

declare(strict_types=1);

namespace Subster\PhpSdk\DataObjects;

use DateTimeInterface;
use Subster\PhpSdk\Concerns\Data;

class RecordSubscriptionUsageEventData extends Data
{
    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        public readonly int $quantity,
        public readonly DateTimeInterface|string|null $occurred_at = null,
        public readonly ?string $idempotency_key = null,
        public readonly ?array $metadata = null,
    ) {}

    public function toArray(): array
    {
        return [
            'quantity' => $this->quantity,
            ...($this->occurred_at !== null ? ['occurred_at' => $this->normalizeValue($this->occurred_at)] : []),
            ...($this->idempotency_key !== null ? ['idempotency_key' => $this->idempotency_key] : []),
            ...($this->metadata !== null ? ['metadata' => $this->metadata] : []),
        ];
    }
}
