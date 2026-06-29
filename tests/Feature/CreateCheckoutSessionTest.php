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
                    'unit' => 'day',
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
        ->and($session->url)->toBe('https://subster.test/checkout/checkout-session-id');

    $mockClient->assertSentCount(1, CreateCheckoutSessionRequest::class);
});
