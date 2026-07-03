<?php

declare(strict_types=1);

namespace Subster\PhpSdk\DataObjects;

use DateTimeImmutable;
use Subster\PhpSdk\Concerns\Collection;
use Subster\PhpSdk\Concerns\Data;
use Subster\PhpSdk\Enums\Currency;
use Subster\PhpSdk\Enums\InvoiceStatus;
use Subster\PhpSdk\Support\DateTimeNormalizer;

class InvoiceData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly InvoiceStatus $status,
        public readonly float $amount,
        public readonly Currency $currency,
        public readonly ?string $description,
        public readonly ?DateTimeImmutable $paid_at,
        public readonly DateTimeImmutable $created_at,
        public readonly DateTimeImmutable $updated_at,
        public readonly InvoiceCustomerData $customer,
        public readonly ?InvoiceSubscriptionData $subscription,
        public readonly Collection $items,
        public readonly float $subtotal_amount = 0.0,
        public readonly float $discount_amount = 0.0,
        public readonly ?InvoiceDiscountData $discount = null,
    ) {}

    public static function fromSaloon(array $response): self
    {
        $amount = floatval($response['amount']);

        return new self(
            id: strval($response['id']),
            status: InvoiceStatus::from(strval($response['status'])),
            amount: $amount,
            currency: Currency::from(strval($response['currency'])),
            description: isset($response['description']) ? strval($response['description']) : null,
            paid_at: isset($response['paid_at']) ? DateTimeNormalizer::parse($response['paid_at']) : null,
            created_at: DateTimeNormalizer::parse($response['created_at']),
            updated_at: DateTimeNormalizer::parse($response['updated_at']),
            customer: InvoiceCustomerData::fromSaloon($response['customer']),
            subscription: isset($response['subscription']) && is_array($response['subscription'])
                ? InvoiceSubscriptionData::fromSaloon($response['subscription'])
                : null,
            items: Collection::make(
                $response['items'],
                fn (array $item): InvoiceItemData => InvoiceItemData::fromSaloon($item),
            ),
            subtotal_amount: isset($response['subtotal_amount']) ? floatval($response['subtotal_amount']) : $amount,
            discount_amount: isset($response['discount_amount']) ? floatval($response['discount_amount']) : 0.0,
            discount: isset($response['discount']) && is_array($response['discount'])
                ? InvoiceDiscountData::fromSaloon($response['discount'])
                : null,
        );
    }
}
