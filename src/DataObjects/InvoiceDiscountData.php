<?php

declare(strict_types=1);

namespace Subster\PhpSdk\DataObjects;

use Subster\PhpSdk\Concerns\Data;
use Subster\PhpSdk\Enums\Currency;

class InvoiceDiscountData extends Data
{
    public function __construct(
        public readonly InvoiceDiscountCouponData $coupon,
        public readonly InvoiceDiscountPromotionCodeData $promotion_code,
        public readonly float $subtotal_amount,
        public readonly float $discount_amount,
        public readonly float $total_amount,
        public readonly Currency $currency,
    ) {}

    public static function fromSaloon(array $response): self
    {
        return new self(
            coupon: InvoiceDiscountCouponData::fromSaloon($response['coupon']),
            promotion_code: InvoiceDiscountPromotionCodeData::fromSaloon($response['promotion_code']),
            subtotal_amount: floatval($response['subtotal_amount']),
            discount_amount: floatval($response['discount_amount']),
            total_amount: floatval($response['total_amount']),
            currency: Currency::from(strval($response['currency'])),
        );
    }
}
