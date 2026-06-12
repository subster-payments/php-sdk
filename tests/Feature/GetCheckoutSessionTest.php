<?php

declare(strict_types=1);

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Subster\PhpSdk\DataObjects\CheckoutSessionStatusData;
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
        ->and($session->status)->toBe('pending')
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
        ->and($session->status)->toBe('completed')
        ->and($session->event)->toBe('subscription.activated')
        ->and($session->data)->toBe($payload);
});
