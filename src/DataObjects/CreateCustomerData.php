<?php

declare(strict_types=1);

namespace Subster\PhpSdk\DataObjects;

use Subster\PhpSdk\Concerns\Data;

class CreateCustomerData extends Data
{
    public function __construct(
        public readonly string $email,
        public readonly ?string $name = null,
    ) {}
}
