<?php

declare(strict_types=1);

namespace Subster\PhpSdk\DataObjects;

use Subster\PhpSdk\Concerns\Data;
use Subster\PhpSdk\Enums\BillingPortalFlow;

class CreateBillingPortalSessionData extends Data
{
    public function __construct(
        public readonly string $customer,
        public readonly ?string $return_url = null,
        public readonly ?BillingPortalFlow $flow = null,
    ) {}
}
