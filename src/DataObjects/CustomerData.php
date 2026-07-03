<?php

declare(strict_types=1);

namespace Subster\PhpSdk\DataObjects;

use Carbon\CarbonImmutable;
use Subster\PhpSdk\Concerns\Data;

class CustomerData extends Data
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
            created_at: CarbonImmutable::parse($response['created_at']),
            updated_at: CarbonImmutable::parse($response['updated_at']),
        );
    }
}
