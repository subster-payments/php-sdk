<?php

declare(strict_types=1);

namespace Subster\PhpSdk\DataObjects;

use Carbon\Carbon;
use Subster\PhpSdk\Concerns\Data;

class CustomerData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $email,
        public readonly Carbon $created_at,
        public readonly Carbon $updated_at,
    ) {}

    public static function fromSaloon(array $response): self
    {
        return new self(
            id: strval($response['id']),
            name: strval($response['name']),
            email: strval($response['email']),
            created_at: Carbon::parse($response['created_at']),
            updated_at: Carbon::parse($response['updated_at']),
        );
    }
}
