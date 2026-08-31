<?php

declare(strict_types=1);

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Subster\PhpSdk\DataObjects\PreviewSubscriptionPlanChangeData;
use Subster\PhpSdk\DataObjects\SubscriptionPlanChangeQuoteData;
use Subster\PhpSdk\Enums\Currency;
use Subster\PhpSdk\Enums\PaymentStrategy;
use Subster\PhpSdk\Enums\SubscriptionPlanChangeMode;
use Subster\PhpSdk\Enums\SubscriptionPlanChangeProrationBehavior;
use Subster\PhpSdk\Requests\PreviewSubscriptionPlanChangeRequest;
use Subster\PhpSdk\SubsterConnector;

it('sends an exact plan change preview request and hydrates the quote', function (): void {
    $mockClient = new MockClient([
        PreviewSubscriptionPlanChangeRequest::class => MockResponse::make([
            'id' => 'quote-id',
            'subscription' => 'subscription-id',
            'current_plan' => 'current-plan-id',
            'target_plan' => 'target-plan-id',
            'mode' => 'checkout',
            'proration_behavior' => 'none',
            'payment_strategy' => 'default_then_checkout',
            'amount_due' => 5000,
            'credit_amount' => 0,
            'currency' => 'RUB',
            'target_plan_unit_amount' => 5000,
            'target_recurring_amount' => 10000,
            'quantity' => 2,
            'effective_at' => '2026-01-16T00:00:00+00:00',
            'quoted_at' => '2026-01-16T00:00:00+00:00',
            'expires_at' => '2026-01-16T00:15:00+00:00',
        ], 201),
    ]);
    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    $quote = $connector->subscriptions()->previewPlanChange(
        'subscription-id',
        PreviewSubscriptionPlanChangeData::from([
            'plan' => 'target-plan-id',
            'proration_behavior' => SubscriptionPlanChangeProrationBehavior::None,
            'payment_strategy' => PaymentStrategy::DefaultThenCheckout,
        ]),
    );

    expect($quote)
        ->toBeInstanceOf(SubscriptionPlanChangeQuoteData::class)
        ->id->toBe('quote-id')
        ->subscription->toBe('subscription-id')
        ->current_plan->toBe('current-plan-id')
        ->target_plan->toBe('target-plan-id')
        ->mode->toBe(SubscriptionPlanChangeMode::Checkout)
        ->proration_behavior->toBe(SubscriptionPlanChangeProrationBehavior::None)
        ->payment_strategy->toBe(PaymentStrategy::DefaultThenCheckout)
        ->amount_due->toBe(5000.0)
        ->credit_amount->toBe(0.0)
        ->currency->toBe(Currency::RUB)
        ->target_plan_unit_amount->toBe(5000.0)
        ->target_recurring_amount->toBe(10000.0)
        ->quantity->toBe(2)
        ->and($quote->effective_at)->toBeInstanceOf(DateTimeImmutable::class)
        ->and($quote->quoted_at->format(DateTimeInterface::ATOM))->toBe('2026-01-16T00:00:00+00:00')
        ->and($quote->expires_at->format(DateTimeInterface::ATOM))->toBe('2026-01-16T00:15:00+00:00');

    $mockClient->assertSent(fn (Request $request): bool => $request instanceof PreviewSubscriptionPlanChangeRequest
        && $request->resolveEndpoint() === 'subscriptions/subscription-id/plan-change-quotes'
        && $request->body()->all() === [
            'plan' => 'target-plan-id',
            'proration_behavior' => 'none',
            'payment_strategy' => 'default_then_checkout',
        ]);
});

it('omits optional preview terms so the API applies defaults', function (): void {
    $request = new PreviewSubscriptionPlanChangeRequest(
        'subscription-id',
        new PreviewSubscriptionPlanChangeData('target-plan-id'),
    );

    expect($request->body()->all())->toBe(['plan' => 'target-plan-id']);
});
