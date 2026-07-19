<?php

declare(strict_types=1);

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Subster\PhpSdk\DataObjects\CheckoutSessionData;
use Subster\PhpSdk\DataObjects\CheckoutSessionTrialData;
use Subster\PhpSdk\DataObjects\CheckoutSessionTrialDurationData;
use Subster\PhpSdk\DataObjects\CreateCheckoutSessionData;
use Subster\PhpSdk\DataObjects\CreateCheckoutSessionItemData;
use Subster\PhpSdk\DataObjects\CreateCheckoutSessionSubscriptionData;
use Subster\PhpSdk\Enums\CheckoutPaymentAttemptState;
use Subster\PhpSdk\Enums\CheckoutPaymentState;
use Subster\PhpSdk\Enums\CheckoutSessionTrialInterval;
use Subster\PhpSdk\Enums\PaymentStrategy;
use Subster\PhpSdk\Requests\CreateCheckoutSessionRequest;
use Subster\PhpSdk\SubsterConnector;

it('keeps checkout session request body unchanged without subscription data', function () {
    $mockClient = new MockClient([
        CreateCheckoutSessionRequest::class => MockResponse::make([
            'id' => 'checkout-session-id',
            'url' => 'https://subster.test/checkout/checkout-session-id',
        ]),
    ]);

    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    $connector->checkoutSessions()->create(CreateCheckoutSessionData::from([
        'customer' => 'customer-id',
        'items' => [
            [
                'plan' => 'plan-id',
            ],
        ],
        'success_url' => 'https://example.ru/success',
        'cancel_url' => 'https://example.ru/cancel',
    ]));

    $mockClient->assertSent(fn (Request $request): bool => $request instanceof CreateCheckoutSessionRequest
            && $request->body()->all() === [
                'customer' => 'customer-id',
                'items' => [
                    [
                        'plan' => 'plan-id',
                    ],
                ],
                'success_url' => 'https://example.ru/success',
                'cancel_url' => 'https://example.ru/cancel',
            ]);
});

it('sends checkout item quantity from raw arrays', function () {
    $mockClient = new MockClient([
        CreateCheckoutSessionRequest::class => MockResponse::make([
            'id' => 'checkout-session-id',
            'url' => 'https://subster.test/checkout/checkout-session-id',
        ]),
    ]);

    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    $connector->checkoutSessions()->create(CreateCheckoutSessionData::from([
        'customer' => 'customer-id',
        'items' => [
            [
                'plan' => 'plan-id',
                'quantity' => 5,
            ],
        ],
        'success_url' => 'https://example.ru/success',
        'cancel_url' => null,
    ]));

    $mockClient->assertSent(fn (Request $request): bool => $request instanceof CreateCheckoutSessionRequest
            && $request->body()->all() === [
                'customer' => 'customer-id',
                'items' => [
                    [
                        'plan' => 'plan-id',
                        'quantity' => 5,
                    ],
                ],
                'success_url' => 'https://example.ru/success',
            ]);
});

it('sends checkout promotion code in request body', function () {
    $mockClient = new MockClient([
        CreateCheckoutSessionRequest::class => MockResponse::make([
            'id' => 'checkout-session-id',
            'url' => 'https://subster.test/checkout/checkout-session-id',
        ]),
    ]);

    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    $connector->checkoutSessions()->create(CreateCheckoutSessionData::from([
        'customer' => 'customer-id',
        'items' => [
            [
                'plan' => 'plan-id',
            ],
        ],
        'success_url' => 'https://example.ru/success',
        'promotion_code' => ' SUMMER25 ',
    ]));

    $mockClient->assertSent(fn (Request $request): bool => $request instanceof CreateCheckoutSessionRequest
            && $request->body()->all() === [
                'customer' => 'customer-id',
                'items' => [
                    [
                        'plan' => 'plan-id',
                    ],
                ],
                'success_url' => 'https://example.ru/success',
                'promotion_code' => 'SUMMER25',
            ]);
});

it('omits blank checkout promotion code from request body', function () {
    $mockClient = new MockClient([
        CreateCheckoutSessionRequest::class => MockResponse::make([
            'id' => 'checkout-session-id',
            'url' => 'https://subster.test/checkout/checkout-session-id',
        ]),
    ]);

    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    $connector->checkoutSessions()->create(CreateCheckoutSessionData::from([
        'customer' => 'customer-id',
        'items' => [
            [
                'plan' => 'plan-id',
            ],
        ],
        'success_url' => 'https://example.ru/success',
        'promotion_code' => '   ',
    ]));

    $mockClient->assertSent(fn (Request $request): bool => $request instanceof CreateCheckoutSessionRequest
            && $request->body()->all() === [
                'customer' => 'customer-id',
                'items' => [
                    [
                        'plan' => 'plan-id',
                    ],
                ],
                'success_url' => 'https://example.ru/success',
            ]);
});

it('keeps zero-like checkout promotion code in request body', function () {
    $mockClient = new MockClient([
        CreateCheckoutSessionRequest::class => MockResponse::make([
            'id' => 'checkout-session-id',
            'url' => 'https://subster.test/checkout/checkout-session-id',
        ]),
    ]);

    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    $connector->checkoutSessions()->create(CreateCheckoutSessionData::from([
        'customer' => 'customer-id',
        'items' => [
            [
                'plan' => 'plan-id',
            ],
        ],
        'success_url' => 'https://example.ru/success',
        'promotion_code' => '0',
    ]));

    $mockClient->assertSent(fn (Request $request): bool => $request instanceof CreateCheckoutSessionRequest
            && $request->body()->all() === [
                'customer' => 'customer-id',
                'items' => [
                    [
                        'plan' => 'plan-id',
                    ],
                ],
                'success_url' => 'https://example.ru/success',
                'promotion_code' => '0',
            ]);
});

it('omits null checkout item quantity from raw arrays', function () {
    $mockClient = new MockClient([
        CreateCheckoutSessionRequest::class => MockResponse::make([
            'id' => 'checkout-session-id',
            'url' => 'https://subster.test/checkout/checkout-session-id',
        ]),
    ]);

    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    $connector->checkoutSessions()->create(CreateCheckoutSessionData::from([
        'customer' => 'customer-id',
        'items' => [
            [
                'plan' => 'plan-id',
                'quantity' => null,
            ],
        ],
        'success_url' => 'https://example.ru/success',
    ]));

    $mockClient->assertSent(fn (Request $request): bool => $request instanceof CreateCheckoutSessionRequest
            && $request->body()->all() === [
                'customer' => 'customer-id',
                'items' => [
                    [
                        'plan' => 'plan-id',
                    ],
                ],
                'success_url' => 'https://example.ru/success',
            ]);
});

it('sends checkout item quantity from item data objects', function () {
    $mockClient = new MockClient([
        CreateCheckoutSessionRequest::class => MockResponse::make([
            'id' => 'checkout-session-id',
            'url' => 'https://subster.test/checkout/checkout-session-id',
        ]),
    ]);

    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    $connector->checkoutSessions()->create(CreateCheckoutSessionData::from([
        'customer' => 'customer-id',
        'items' => [
            CreateCheckoutSessionItemData::from([
                'plan' => 'plan-id',
                'quantity' => 5,
            ]),
        ],
        'success_url' => 'https://example.ru/success',
    ]));

    $mockClient->assertSent(fn (Request $request): bool => $request instanceof CreateCheckoutSessionRequest
            && $request->body()->all() === [
                'customer' => 'customer-id',
                'items' => [
                    [
                        'plan' => 'plan-id',
                        'quantity' => 5,
                    ],
                ],
                'success_url' => 'https://example.ru/success',
            ]);
});

it('sends paid trial subscription data in checkout session request body', function () {
    $mockClient = new MockClient([
        CreateCheckoutSessionRequest::class => MockResponse::make([
            'id' => 'checkout-session-id',
            'url' => 'https://subster.test/checkout/checkout-session-id',
        ]),
    ]);

    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    $connector->checkoutSessions()->create(CreateCheckoutSessionData::from([
        'customer' => 'customer-id',
        'items' => [
            CreateCheckoutSessionItemData::from([
                'plan' => 'plan-id',
            ]),
        ],
        'subscription_data' => CreateCheckoutSessionSubscriptionData::from([
            'trial' => CheckoutSessionTrialData::from([
                'amount' => 100,
                'duration' => CheckoutSessionTrialDurationData::from([
                    'unit' => CheckoutSessionTrialInterval::Day,
                    'count' => 14,
                ]),
            ]),
        ]),
        'success_url' => 'https://example.ru/success',
        'cancel_url' => 'https://example.ru/cancel',
    ]));

    $mockClient->assertSent(fn (Request $request): bool => $request instanceof CreateCheckoutSessionRequest
            && $request->body()->all() === [
                'customer' => 'customer-id',
                'items' => [
                    [
                        'plan' => 'plan-id',
                    ],
                ],
                'success_url' => 'https://example.ru/success',
                'cancel_url' => 'https://example.ru/cancel',
                'subscription_data' => [
                    'trial' => [
                        'amount' => 100,
                        'duration' => [
                            'unit' => 'day',
                            'count' => 14,
                        ],
                    ],
                ],
            ]);
});

it('returns checkout session data from a mocked Saloon response', function () {
    $mockClient = new MockClient([
        CreateCheckoutSessionRequest::class => MockResponse::make([
            'id' => 'checkout-session-id',
            'url' => 'https://subster.test/checkout/checkout-session-id',
        ]),
    ]);

    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    $session = $connector->checkoutSessions()->create(CreateCheckoutSessionData::from([
        'customer' => 'customer-id',
        'items' => [
            [
                'plan' => 'plan-id',
            ],
        ],
        'success_url' => 'https://example.ru/success',
        'cancel_url' => null,
    ]));

    expect($session)
        ->toBeInstanceOf(CheckoutSessionData::class)
        ->and($session->id)->toBe('checkout-session-id')
        ->and($session->url)->toBe('https://subster.test/checkout/checkout-session-id')
        ->and($session->checkout_url)->toBe('https://subster.test/checkout/checkout-session-id')
        ->and($session->payment_state)->toBe(CheckoutPaymentState::RequiresPayment);

    $mockClient->assertSentCount(1, CreateCheckoutSessionRequest::class);
});

it('sends default then checkout strategy and idempotency header', function (): void {
    $mockClient = new MockClient([
        CreateCheckoutSessionRequest::class => MockResponse::make([
            'id' => 'checkout-session-id',
            'payment_state' => 'requires_payment',
            'payment_attempt_state' => 'failed',
            'checkout_url' => 'https://subster.test/checkout/checkout-session-id',
            'amount' => 400,
            'currency' => 'RUB',
            'url' => 'https://subster.test/checkout/checkout-session-id',
        ]),
    ]);

    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    $session = $connector->checkoutSessions()->create(CreateCheckoutSessionData::from([
        'customer' => 'customer-id',
        'items' => [['plan' => 'plan-id', 'quantity' => 5]],
        'success_url' => 'https://example.ru/success',
        'payment_strategy' => PaymentStrategy::DefaultThenCheckout,
        'idempotency_key' => 'top-up-1',
    ]));

    expect($session)
        ->payment_attempt_state->toBe(CheckoutPaymentAttemptState::Failed)
        ->amount->toBe(400.0)
        ->currency->toBe('RUB');

    $mockClient->assertSent(fn (Request $request): bool => $request instanceof CreateCheckoutSessionRequest
        && $request->body()->all()['payment_strategy'] === PaymentStrategy::DefaultThenCheckout->value
        && $request->headers()->get('Idempotency-Key') === 'top-up-1');
});

it('hydrates a synchronously paid checkout without a redirect url', function (): void {
    $session = CheckoutSessionData::fromSaloon([
        'id' => 'checkout-session-id',
        'url' => null,
        'checkout_url' => null,
        'payment_state' => 'paid',
        'payment_attempt_state' => 'succeeded',
        'amount' => 390,
        'currency' => 'RUB',
    ]);

    expect($session)
        ->payment_state->toBe(CheckoutPaymentState::Paid)
        ->payment_attempt_state->toBe(CheckoutPaymentAttemptState::Succeeded)
        ->amount->toBe(390.0)
        ->currency->toBe('RUB')
        ->checkout_url->toBeNull()
        ->url->toBe('');
});
