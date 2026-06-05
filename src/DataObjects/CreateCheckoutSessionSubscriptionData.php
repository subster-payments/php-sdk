<?php

declare(strict_types=1);

namespace Subster\PhpSdk\DataObjects;

use Subster\PhpSdk\Concerns\Data;

class CreateCheckoutSessionSubscriptionData extends Data
{
    public function __construct(
        public readonly ?CheckoutSessionTrialData $trial = null,
    ) {}
}
