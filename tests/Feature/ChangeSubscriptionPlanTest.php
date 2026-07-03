<?php

declare(strict_types=1);

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Subster\PhpSdk\DataObjects\ChangeSubscriptionPlanData;
use Subster\PhpSdk\DataObjects\SubscriptionPlanChangeData;
use Subster\PhpSdk\Enums\SubscriptionPlanChangeMode;
use Subster\PhpSdk\Enums\SubscriptionPlanChangeProrationBehavior;
use Subster\PhpSdk\Requests\ChangeSubscriptionPlanRequest;
use Subster\PhpSdk\SubsterConnector;

it('sends subscription plan change checkout request body', function () {
    $mockClient = new MockClient([
        ChangeSubscriptionPlanRequest::class => MockResponse::make([
            'id' => 'change-id',
            'mode' => 'checkout',
            'checkout_session' => 'checkout-session-id',
            'checkout_url' => 'https://subster.test/checkout/checkout-session-id',
            'amount_due' => 4750,
            'credit_amount' => 250,
            'effective_at' => '2026-01-16T00:00:00+00:00',
        ]),
    ]);

    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    $connector->subscriptions()->changePlan('subscription-id', ChangeSubscriptionPlanData::from([
        'plan' => 'target-plan-id',
        'success_url' => 'https://example.ru/success',
        'cancel_url' => 'https://example.ru/cancel',
    ]));

    $mockClient->assertSent(fn (Request $request): bool => $request instanceof ChangeSubscriptionPlanRequest
        && $request->resolveEndpoint() === 'subscriptions/subscription-id/change-plan'
        && $request->body()->all() === [
            'plan' => 'target-plan-id',
            'success_url' => 'https://example.ru/success',
            'cancel_url' => 'https://example.ru/cancel',
        ]);
});

it('omits optional checkout urls for scheduled subscription plan changes', function () {
    $mockClient = new MockClient([
        ChangeSubscriptionPlanRequest::class => MockResponse::make([
            'id' => 'change-id',
            'mode' => 'scheduled',
            'checkout_session' => null,
            'checkout_url' => null,
            'amount_due' => 0,
            'credit_amount' => 0,
            'effective_at' => '2026-02-01T00:00:00+00:00',
        ]),
    ]);

    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    $connector->subscriptions()->changePlan('subscription-id', ChangeSubscriptionPlanData::from([
        'plan' => 'target-plan-id',
    ]));

    $mockClient->assertSent(fn (Request $request): bool => $request instanceof ChangeSubscriptionPlanRequest
        && $request->body()->all() === [
            'plan' => 'target-plan-id',
        ]);
});

it('sends optional subscription plan change proration behavior', function () {
    $mockClient = new MockClient([
        ChangeSubscriptionPlanRequest::class => MockResponse::make([
            'id' => 'change-id',
            'mode' => 'checkout',
            'checkout_session' => 'checkout-session-id',
            'checkout_url' => 'https://subster.test/checkout/checkout-session-id',
            'proration_behavior' => 'none',
            'amount_due' => 5000,
            'credit_amount' => 0,
            'effective_at' => '2026-01-16T00:00:00+00:00',
        ]),
    ]);

    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    $connector->subscriptions()->changePlan('subscription-id', ChangeSubscriptionPlanData::from([
        'plan' => 'target-plan-id',
        'success_url' => 'https://example.ru/success',
        'proration_behavior' => SubscriptionPlanChangeProrationBehavior::None,
    ]));

    $mockClient->assertSent(fn (Request $request): bool => $request instanceof ChangeSubscriptionPlanRequest
        && $request->body()->all() === [
            'plan' => 'target-plan-id',
            'success_url' => 'https://example.ru/success',
            'proration_behavior' => 'none',
        ]);
});

it('returns subscription plan change data from a mocked Saloon response', function () {
    $mockClient = new MockClient([
        ChangeSubscriptionPlanRequest::class => MockResponse::make([
            'id' => 'change-id',
            'mode' => 'checkout',
            'checkout_session' => 'checkout-session-id',
            'checkout_url' => 'https://subster.test/checkout/checkout-session-id',
            'proration_behavior' => 'none',
            'amount_due' => 4750,
            'credit_amount' => 250,
            'effective_at' => '2026-01-16T00:00:00+00:00',
        ]),
    ]);

    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    $change = $connector->subscriptions()->changePlan('subscription-id', ChangeSubscriptionPlanData::from([
        'plan' => 'target-plan-id',
        'success_url' => 'https://example.ru/success',
    ]));

    expect($change)
        ->toBeInstanceOf(SubscriptionPlanChangeData::class)
        ->and($change->mode)->toBe(SubscriptionPlanChangeMode::Checkout)
        ->and($change->id)->toBe('change-id')
        ->and($change->checkout_session)->toBe('checkout-session-id')
        ->and($change->checkout_url)->toBe('https://subster.test/checkout/checkout-session-id')
        ->and($change->proration_behavior)->toBe(SubscriptionPlanChangeProrationBehavior::None)
        ->and($change->amount_due)->toBe(4750.0)
        ->and($change->credit_amount)->toBe(250.0)
        ->and($change->effective_at)->toBeInstanceOf(DateTimeImmutable::class)
        ->and($change->effective_at->format(DateTimeInterface::ATOM))->toBe('2026-01-16T00:00:00+00:00');

    $mockClient->assertSentCount(1, ChangeSubscriptionPlanRequest::class);
});

it('hydrates legacy subscription plan change responses without proration behavior', function () {
    $change = SubscriptionPlanChangeData::fromSaloon([
        'mode' => 'checkout',
        'change' => 'change-id',
        'id' => 'checkout-session-id',
        'url' => 'https://subster.test/checkout/checkout-session-id',
        'amount_due' => 4750,
        'credit_amount' => 250,
        'effective_at' => '2026-01-16T00:00:00+00:00',
    ]);

    expect($change->id)->toBe('change-id')
        ->and($change->checkout_session)->toBe('checkout-session-id')
        ->and($change->checkout_url)->toBe('https://subster.test/checkout/checkout-session-id')
        ->and($change->proration_behavior)->toBeNull();
});
