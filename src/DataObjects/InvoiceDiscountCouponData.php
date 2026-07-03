<?php

declare(strict_types=1);

namespace Subster\PhpSdk\DataObjects;

use Subster\PhpSdk\Concerns\Data;
use Subster\PhpSdk\Enums\CouponDiscountType;
use Subster\PhpSdk\Enums\CouponDuration;

class InvoiceDiscountCouponData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $api_identifier,
        public readonly string $name,
        public readonly CouponDiscountType $discount_type,
        public readonly ?float $percent_off,
        public readonly ?float $amount_off,
        public readonly CouponDuration $duration,
        public readonly ?int $duration_in_months,
    ) {}

    public static function fromSaloon(array $response): self
    {
        return new self(
            id: strval($response['id']),
            api_identifier: isset($response['api_identifier'])
                ? strval($response['api_identifier'])
                : (isset($response['api_id']) ? strval($response['api_id']) : null),
            name: strval($response['name']),
            discount_type: CouponDiscountType::from(strval($response['discount_type'])),
            percent_off: isset($response['percent_off']) ? floatval($response['percent_off']) : null,
            amount_off: isset($response['amount_off']) ? floatval($response['amount_off']) : null,
            duration: CouponDuration::from(strval($response['duration'])),
            duration_in_months: isset($response['duration_in_months']) ? (int) $response['duration_in_months'] : null,
        );
    }
}
