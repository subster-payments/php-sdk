<?php

declare(strict_types=1);

namespace Subster\PhpSdk\DataObjects;

use Subster\PhpSdk\Concerns\Data;

class CheckoutSessionStatusData extends Data
{
    /**
     * @param  array<string, mixed>|null  $data
     */
    public function __construct(
        public readonly string $id,
        public readonly string $status,
        public readonly ?string $event,
        public readonly ?array $data,
    ) {}

    public static function fromSaloon(array $response): self
    {
        return new self(
            id: strval($response['id']),
            status: strval($response['status']),
            event: isset($response['event']) ? strval($response['event']) : null,
            data: isset($response['data']) && is_array($response['data']) ? $response['data'] : null,
        );
    }
}
