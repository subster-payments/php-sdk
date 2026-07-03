<?php

declare(strict_types=1);

namespace Subster\PhpSdk\DataObjects;

use Carbon\CarbonImmutable;
use Subster\PhpSdk\Concerns\Data;

class InvoiceCustomerData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $name,
        public readonly string $email,
        public readonly CarbonImmutable $created_at,
        public readonly CarbonImmutable $updated_at,
    ) {}

    public static function fromSaloon(array $response): self
    {
        return new self(
            id: strval($response['id']),
            name: isset($response['name']) ? strval($response['name']) : null,
            email: strval($response['email']),
            created_at: CarbonImmutable::createFromTimestamp((int) $response['created_at'], 'UTC'),
            updated_at: CarbonImmutable::createFromTimestamp((int) $response['updated_at'], 'UTC'),
        );
    }
}
