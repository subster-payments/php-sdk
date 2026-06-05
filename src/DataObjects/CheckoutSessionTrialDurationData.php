<?php

declare(strict_types=1);

namespace Subster\PhpSdk\DataObjects;

use Subster\PhpSdk\Concerns\Data;

class CheckoutSessionTrialDurationData extends Data
{
    public function __construct(
        public readonly string $unit,
        public readonly int $count,
    ) {}
}
