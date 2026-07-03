<?php

declare(strict_types=1);

namespace Subster\PhpSdk\DataObjects;

use DateTimeImmutable;
use Subster\PhpSdk\Concerns\Data;
use Subster\PhpSdk\Support\DateTimeNormalizer;

class CustomerData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $name,
        public readonly string $email,
        public readonly DateTimeImmutable $created_at,
        public readonly DateTimeImmutable $updated_at,
    ) {}

    public static function fromSaloon(array $response): self
    {
        return new self(
            id: strval($response['id']),
            name: isset($response['name']) ? strval($response['name']) : null,
            email: strval($response['email']),
            created_at: DateTimeNormalizer::parse($response['created_at']),
            updated_at: DateTimeNormalizer::parse($response['updated_at']),
        );
    }
}
