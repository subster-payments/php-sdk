<?php

declare(strict_types=1);

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Subster\PhpSdk\DataObjects\CheckoutSessionStatusData;
use Subster\PhpSdk\Enums\CheckoutPaymentAttemptState;
use Subster\PhpSdk\Enums\CheckoutPaymentState;
use Subster\PhpSdk\Enums\CheckoutSessionStatus;
use Subster\PhpSdk\Enums\WebhookEndpointEvent;
use Subster\PhpSdk\Requests\GetCheckoutSessionRequest;
use Subster\PhpSdk\SubsterConnector;

it('sends checkout session status request', function () {
    $mockClient = new MockClient([
        GetCheckoutSessionRequest::class => MockResponse::make([
            'id' => 'checkout-session-id',
            'status' => 'pending',
            'event' => null,
            'data' => null,
        ]),
    ]);

    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    $connector->checkoutSessions()->get('checkout-session-id');

    $mockClient->assertSent(fn (Request $request): bool => $request instanceof GetCheckoutSessionRequest
        && $request->resolveEndpoint() === 'checkout/session/checkout-session-id');
});

it('returns pending checkout session status data from a mocked Saloon response', function () {
    $mockClient = new MockClient([
        GetCheckoutSessionRequest::class => MockResponse::make([
            'id' => 'checkout-session-id',
            'status' => 'pending',
            'event' => null,
            'data' => null,
        ]),
    ]);

    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    $session = $connector->checkoutSessions()->get('checkout-session-id');

    expect($session)
        ->toBeInstanceOf(CheckoutSessionStatusData::class)
        ->and($session->id)->toBe('checkout-session-id')
        ->and($session->status)->toBe(CheckoutSessionStatus::Pending)
        ->and($session->payment_state)->toBe(CheckoutPaymentState::RequiresPayment)
        ->and($session->checkout_url)->toBeNull()
        ->and($session->event)->toBeNull()
        ->and($session->data)->toBeNull();
});

it('returns completed checkout session event data from a mocked Saloon response', function () {
    $payload = [
        'subscription' => 'subscription-id',
        'customer' => 'customer-id',
        'plan' => 'plan-id',
        'starts_at' => '2026-01-16T00:00:00+00:00',
        'ends_at' => '2026-02-16T00:00:00+00:00',
        'grace_ends_at' => '2026-02-21T00:00:00+00:00',
    ];

    $mockClient = new MockClient([
        GetCheckoutSessionRequest::class => MockResponse::make([
            'id' => 'checkout-session-id',
            'status' => 'completed',
            'event' => 'subscription.activated',
            'data' => $payload,
        ]),
    ]);

    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    $session = $connector->checkoutSessions()->get('checkout-session-id');

    expect($session)
        ->toBeInstanceOf(CheckoutSessionStatusData::class)
        ->and($session->id)->toBe('checkout-session-id')
        ->and($session->status)->toBe(CheckoutSessionStatus::Completed)
        ->and($session->payment_state)->toBe(CheckoutPaymentState::Paid)
        ->and($session->checkout_url)->toBeNull()
        ->and($session->event)->toBe(WebhookEndpointEvent::SubscriptionActivated)
        ->and($session->data)->toBe($payload);
});

it('hydrates a processing payment attempt with its invoice snapshot', function (): void {
    $session = CheckoutSessionStatusData::fromSaloon([
        'id' => 'checkout-session-id',
        'status' => 'pending',
        'payment_state' => 'requires_payment',
        'payment_attempt_state' => 'processing',
        'checkout_url' => null,
        'amount' => 390,
        'currency' => 'RUB',
        'event' => null,
        'data' => null,
    ]);

    expect($session)
        ->payment_attempt_state->toBe(CheckoutPaymentAttemptState::Processing)
        ->amount->toBe(390.0)
        ->currency->toBe('RUB')
        ->checkout_url->toBeNull();
});

it('returns completed one-time checkout session event data from a mocked Saloon response', function () {
    $payload = [
        'checkout_session' => 'checkout-session-id',
        'invoice' => 'invoice-id',
        'customer' => 'customer-id',
        'amount' => 400,
        'subtotal_amount' => 500,
        'discount_amount' => 100,
        'discount' => [
            'coupon' => [
                'id' => 'coupon-id',
                'api_identifier' => null,
                'name' => 'Summer Sale',
                'discount_type' => 'fixed_amount',
                'percent_off' => null,
                'amount_off' => 100,
                'duration' => 'once',
                'duration_in_months' => null,
            ],
            'promotion_code' => [
                'id' => 'promotion-code-id',
                'code' => 'SUMMER100',
            ],
            'subtotal_amount' => 500,
            'discount_amount' => 100,
            'total_amount' => 400,
            'currency' => 'RUB',
        ],
        'currency' => 'RUB',
        'items' => [
            [
                'plan' => 'plan-id',
                'product_name' => 'Tokens',
                'type' => 'one_time',
                'unit_amount' => 80,
                'quantity' => 5,
                'amount' => 400,
            ],
        ],
    ];

    $mockClient = new MockClient([
        GetCheckoutSessionRequest::class => MockResponse::make([
            'id' => 'checkout-session-id',
            'status' => 'completed',
            'event' => 'checkout.session.completed',
            'data' => $payload,
        ]),
    ]);

    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    $session = $connector->checkoutSessions()->get('checkout-session-id');

    expect($session)
        ->toBeInstanceOf(CheckoutSessionStatusData::class)
        ->and($session->id)->toBe('checkout-session-id')
        ->and($session->status)->toBe(CheckoutSessionStatus::Completed)
        ->and($session->event)->toBe(WebhookEndpointEvent::CheckoutSessionCompleted)
        ->and($session->data)->toBe($payload)
        ->and($session->data['discount_amount'])->toBe(100)
        ->and($session->data['discount']['promotion_code']['code'])->toBe('SUMMER100')
        ->and($session->data['items'][0]['quantity'])->toBe(5);
});

it('preserves checkout session item pricing model in raw completed event data', function () {
    $payload = [
        'checkout_session' => 'checkout-session-id',
        'invoice' => 'invoice-id',
        'customer' => 'customer-id',
        'amount' => 2000,
        'currency' => 'RUB',
        'items' => [
            [
                'plan' => 'plan-id',
                'product_name' => 'Team Seats',
                'type' => 'recurring',
                'pricing_model' => 'usage_based',
                'unit_amount' => 100,
                'quantity' => 20,
                'amount' => 2000,
            ],
        ],
    ];

    $mockClient = new MockClient([
        GetCheckoutSessionRequest::class => MockResponse::make([
            'id' => 'checkout-session-id',
            'status' => 'completed',
            'event' => 'checkout.session.completed',
            'data' => $payload,
        ]),
    ]);

    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    $session = $connector->checkoutSessions()->get('checkout-session-id');

    expect($session->data)->toBe($payload)
        ->and($session->data['items'][0]['pricing_model'])->toBe('usage_based')
        ->and($session->data['items'][0]['quantity'])->toBe(20);
});

it('hydrates canceled and expired checkout terminal statuses', function (string $status): void {
    $session = CheckoutSessionStatusData::fromSaloon([
        'id' => 'checkout-session-id',
        'status' => $status,
        'payment_state' => 'requires_payment',
        'checkout_url' => null,
        'event' => 'checkout.session.closed',
        'data' => ['status' => $status],
    ]);

    expect($session)
        ->status->toBe(CheckoutSessionStatus::from($status))
        ->payment_state->toBe(CheckoutPaymentState::RequiresPayment)
        ->checkout_url->toBeNull()
        ->event->toBe(WebhookEndpointEvent::CheckoutSessionClosed)
        ->and($session->data['status'])->toBe($status);
})->with(['canceled', 'expired']);
