<?php

declare(strict_types=1);

namespace Subster\PhpSdk\DataObjects;

use DateTimeInterface;
use Subster\PhpSdk\Concerns\Data;

class ListInvoicesData extends Data
{
    public function __construct(
        public readonly ?int $limit = null,
        public readonly ?string $starting_after = null,
        public readonly ?string $ending_before = null,
        public readonly ?string $customer = null,
        public readonly ?string $subscription = null,
        public readonly DateTimeInterface|string|null $paid_at_gte = null,
        public readonly DateTimeInterface|string|null $paid_at_lte = null,
    ) {}
}
