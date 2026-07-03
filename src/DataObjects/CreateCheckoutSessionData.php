<?php

declare(strict_types=1);

namespace Subster\PhpSdk\DataObjects;

use Subster\PhpSdk\Concerns\Data;

class CreateCheckoutSessionData extends Data
{
    /**
     * @param  array<int, array{plan: string, quantity?: int|null}|CreateCheckoutSessionItemData>  $items
     */
    public function __construct(
        public readonly string $customer,
        public readonly array $items,
        public readonly string $success_url,
        public readonly ?string $cancel_url = null,
        public readonly ?CreateCheckoutSessionSubscriptionData $subscription_data = null,
        public readonly ?string $promotion_code = null,
    ) {}
}
