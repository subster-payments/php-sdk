<?php

declare(strict_types=1);

namespace Subster\PhpSdk\DataObjects;

use Subster\PhpSdk\Concerns\Data;
use Subster\PhpSdk\Enums\CheckoutPaymentAttemptState;
use Subster\PhpSdk\Enums\CheckoutPaymentState;

class CheckoutSessionData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $url,
        public readonly CheckoutPaymentState $payment_state = CheckoutPaymentState::RequiresPayment,
        public readonly ?string $checkout_url = null,
        public readonly ?CheckoutPaymentAttemptState $payment_attempt_state = null,
        public readonly ?float $amount = null,
        public readonly ?string $currency = null,
    ) {}

    public static function fromSaloon(array $response): self
    {
        $checkoutUrl = isset($response['checkout_url'])
            ? strval($response['checkout_url'])
            : (isset($response['url']) ? strval($response['url']) : null);

        return new self(
            id: strval($response['id']),
            url: isset($response['url']) ? strval($response['url']) : ($checkoutUrl ?? ''),
            payment_state: isset($response['payment_state'])
                ? CheckoutPaymentState::from(strval($response['payment_state']))
                : CheckoutPaymentState::RequiresPayment,
            checkout_url: $checkoutUrl,
            payment_attempt_state: isset($response['payment_attempt_state'])
                ? CheckoutPaymentAttemptState::from(strval($response['payment_attempt_state']))
                : null,
            amount: isset($response['amount']) ? floatval($response['amount']) : null,
            currency: isset($response['currency']) ? strval($response['currency']) : null,
        );
    }
}
