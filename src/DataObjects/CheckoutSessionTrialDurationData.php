<?php

declare(strict_types=1);

namespace Subster\PhpSdk\DataObjects;

use Subster\PhpSdk\Concerns\Data;
use Subster\PhpSdk\Enums\CheckoutSessionTrialInterval;

class CheckoutSessionTrialDurationData extends Data
{
    public function __construct(
        public readonly CheckoutSessionTrialInterval $unit,
        public readonly int $count,
    ) {}
}
