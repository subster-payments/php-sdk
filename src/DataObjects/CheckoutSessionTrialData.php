<?php

declare(strict_types=1);

namespace Subster\PhpSdk\DataObjects;

use Subster\PhpSdk\Concerns\Data;

class CheckoutSessionTrialData extends Data
{
    public function __construct(
        public readonly int|float $amount,
        public readonly CheckoutSessionTrialDurationData $duration,
    ) {}
}
