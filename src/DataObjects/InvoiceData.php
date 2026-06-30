<?php

declare(strict_types=1);

namespace Subster\PhpSdk\DataObjects;

use Carbon\Carbon;
use Subster\PhpSdk\Concerns\Collection;
use Subster\PhpSdk\Concerns\Data;

class InvoiceData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $status,
        public readonly float $amount,
        public readonly string $currency,
        public readonly ?string $description,
        public readonly ?Carbon $paid_at,
        public readonly Carbon $created_at,
        public readonly Carbon $updated_at,
        public readonly InvoiceCustomerData $customer,
        public readonly ?InvoiceSubscriptionData $subscription,
        public readonly Collection $items,
    ) {}

    public static function fromSaloon(array $response): self
    {
        return new self(
            id: strval($response['id']),
            status: strval($response['status']),
            amount: floatval($response['amount']),
            currency: strval($response['currency']),
            description: isset($response['description']) ? strval($response['description']) : null,
            paid_at: isset($response['paid_at']) ? Carbon::parse($response['paid_at']) : null,
            created_at: Carbon::createFromTimestamp((int) $response['created_at'], 'UTC'),
            updated_at: Carbon::createFromTimestamp((int) $response['updated_at'], 'UTC'),
            customer: InvoiceCustomerData::fromSaloon($response['customer']),
            subscription: isset($response['subscription']) && is_array($response['subscription'])
                ? InvoiceSubscriptionData::fromSaloon($response['subscription'])
                : null,
            items: Collection::make(
                $response['items'],
                fn (array $item): InvoiceItemData => InvoiceItemData::fromSaloon($item),
            ),
        );
    }
}
