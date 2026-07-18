<?php

declare(strict_types=1);

namespace Subster\PhpSdk\DataObjects;

use Subster\PhpSdk\Concerns\Data;
use Subster\PhpSdk\Enums\CheckoutPaymentState;
use Subster\PhpSdk\Enums\CheckoutSessionStatus;
use Subster\PhpSdk\Enums\WebhookEndpointEvent;

class CheckoutSessionStatusData extends Data
{
    /**
     * @param  array<string, mixed>|null  $data
     */
    public function __construct(
        public readonly string $id,
        public readonly CheckoutSessionStatus $status,
        public readonly ?WebhookEndpointEvent $event,
        public readonly ?array $data,
        public readonly CheckoutPaymentState $payment_state = CheckoutPaymentState::RequiresPayment,
        public readonly ?string $checkout_url = null,
    ) {}

    public static function fromSaloon(array $response): self
    {
        return new self(
            id: strval($response['id']),
            status: CheckoutSessionStatus::from(strval($response['status'])),
            event: isset($response['event']) ? WebhookEndpointEvent::from(strval($response['event'])) : null,
            data: isset($response['data']) && is_array($response['data']) ? $response['data'] : null,
            payment_state: isset($response['payment_state'])
                ? CheckoutPaymentState::from(strval($response['payment_state']))
                : ($response['status'] === CheckoutSessionStatus::Completed->value
                    ? CheckoutPaymentState::Paid
                    : CheckoutPaymentState::RequiresPayment),
            checkout_url: isset($response['checkout_url']) ? strval($response['checkout_url']) : null,
        );
    }
}
