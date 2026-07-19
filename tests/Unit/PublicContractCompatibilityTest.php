<?php

declare(strict_types=1);

use Subster\PhpSdk\DataObjects\ChangeSubscriptionPlanData;
use Subster\PhpSdk\DataObjects\CheckoutSessionData;
use Subster\PhpSdk\DataObjects\CheckoutSessionStatusData;
use Subster\PhpSdk\DataObjects\CreateBillingPortalSessionData;
use Subster\PhpSdk\DataObjects\CreateCheckoutSessionData;
use Subster\PhpSdk\DataObjects\SubscriptionPlanChangeData;
use Subster\PhpSdk\Enums\BillingPortalFlow;
use Subster\PhpSdk\Enums\CheckoutPaymentAttemptState;
use Subster\PhpSdk\Enums\CheckoutPaymentState;
use Subster\PhpSdk\Enums\CheckoutSessionStatus;
use Subster\PhpSdk\Enums\PaymentStrategy;
use Subster\PhpSdk\Enums\SubscriptionPlanChangeMode;
use Subster\PhpSdk\Enums\SubscriptionPlanChangeProrationBehavior;
use Subster\PhpSdk\Enums\WebhookEndpointEvent;

it('keeps legacy positional constructor calls compatible', function (): void {
    $effectiveAt = new DateTimeImmutable('2026-01-16T00:00:00+00:00');

    $checkoutRequest = new CreateCheckoutSessionData(
        'customer-id',
        [['plan' => 'plan-id']],
        'https://example.ru/success',
        'https://example.ru/cancel',
        null,
        'SUMMER25',
    );
    $planChangeRequest = new ChangeSubscriptionPlanData(
        'plan-id',
        'https://example.ru/success',
        'https://example.ru/cancel',
        SubscriptionPlanChangeProrationBehavior::None,
    );
    $portalRequest = new CreateBillingPortalSessionData('customer-id', 'https://example.ru/billing');
    $checkout = new CheckoutSessionData('checkout-session-id', 'https://subster.test/checkout/checkout-session-id');
    $status = new CheckoutSessionStatusData(
        'checkout-session-id',
        CheckoutSessionStatus::Pending,
        null,
        null,
    );
    $planChange = new SubscriptionPlanChangeData(
        SubscriptionPlanChangeMode::Checkout,
        'change-id',
        'checkout-session-id',
        'https://subster.test/checkout/checkout-session-id',
        100.0,
        0.0,
        $effectiveAt,
        SubscriptionPlanChangeProrationBehavior::None,
    );

    expect($checkoutRequest->payment_strategy)->toBeNull()
        ->and($checkoutRequest->idempotency_key)->toBeNull()
        ->and($planChangeRequest->payment_strategy)->toBeNull()
        ->and($planChangeRequest->idempotency_key)->toBeNull()
        ->and($portalRequest->flow)->toBeNull()
        ->and($checkout->payment_state)->toBe(CheckoutPaymentState::RequiresPayment)
        ->and($checkout->checkout_url)->toBeNull()
        ->and($checkout->payment_attempt_state)->toBeNull()
        ->and($checkout->amount)->toBeNull()
        ->and($checkout->currency)->toBeNull()
        ->and($status->payment_state)->toBe(CheckoutPaymentState::RequiresPayment)
        ->and($status->checkout_url)->toBeNull()
        ->and($status->payment_attempt_state)->toBeNull()
        ->and($status->amount)->toBeNull()
        ->and($status->currency)->toBeNull()
        ->and($planChange->payment_state)->toBe(CheckoutPaymentState::RequiresPayment)
        ->and($planChange->payment_attempt_state)->toBeNull()
        ->and($planChange->currency)->toBeNull()
        ->and($planChange->applied)->toBeFalse();

    $legacyUrlType = (new ReflectionProperty(CheckoutSessionData::class, 'url'))->getType();

    expect($legacyUrlType)->toBeInstanceOf(ReflectionNamedType::class)
        ->and($legacyUrlType->getName())->toBe('string')
        ->and($legacyUrlType->allowsNull())->toBeFalse();
});

it('keeps legacy named constructor calls compatible', function (): void {
    $effectiveAt = new DateTimeImmutable('2026-01-16T00:00:00+00:00');

    expect(new CreateCheckoutSessionData(
        customer: 'customer-id',
        items: [['plan' => 'plan-id']],
        success_url: 'https://example.ru/success',
        cancel_url: null,
        subscription_data: null,
        promotion_code: null,
    ))->toBeInstanceOf(CreateCheckoutSessionData::class)
        ->and(new ChangeSubscriptionPlanData(
            plan: 'plan-id',
            success_url: null,
            cancel_url: null,
            proration_behavior: null,
        ))->toBeInstanceOf(ChangeSubscriptionPlanData::class)
        ->and(new CreateBillingPortalSessionData(
            customer: 'customer-id',
            return_url: null,
        ))->toBeInstanceOf(CreateBillingPortalSessionData::class)
        ->and(new CheckoutSessionData(
            id: 'checkout-session-id',
            url: 'https://subster.test/checkout/checkout-session-id',
        ))->toBeInstanceOf(CheckoutSessionData::class)
        ->and(new CheckoutSessionStatusData(
            id: 'checkout-session-id',
            status: CheckoutSessionStatus::Pending,
            event: null,
            data: null,
        ))->toBeInstanceOf(CheckoutSessionStatusData::class)
        ->and(new SubscriptionPlanChangeData(
            mode: SubscriptionPlanChangeMode::Checkout,
            id: 'change-id',
            checkout_session: 'checkout-session-id',
            checkout_url: 'https://subster.test/checkout/checkout-session-id',
            amount_due: 100.0,
            credit_amount: 0.0,
            effective_at: $effectiveAt,
            proration_behavior: null,
        ))->toBeInstanceOf(SubscriptionPlanChangeData::class);
});

it('hydrates new request enums from wire values', function (): void {
    $checkout = CreateCheckoutSessionData::from([
        'customer' => 'customer-id',
        'items' => [['plan' => 'plan-id']],
        'success_url' => 'https://example.ru/success',
        'payment_strategy' => 'default_then_checkout',
    ]);
    $planChange = ChangeSubscriptionPlanData::from([
        'plan' => 'plan-id',
        'payment_strategy' => 'hosted_checkout',
    ]);
    $portal = CreateBillingPortalSessionData::from([
        'customer' => 'customer-id',
        'flow' => 'payment_recovery',
    ]);

    expect($checkout->payment_strategy)->toBe(PaymentStrategy::DefaultThenCheckout)
        ->and($planChange->payment_strategy)->toBe(PaymentStrategy::HostedCheckout)
        ->and($portal->flow)->toBe(BillingPortalFlow::PaymentRecovery);
});

it('matches finite API values', function (): void {
    expect(array_column(PaymentStrategy::cases(), 'value'))->toBe([
        'hosted_checkout',
        'default_then_checkout',
    ])->and(array_column(CheckoutPaymentState::cases(), 'value'))->toBe([
        'paid',
        'requires_payment',
    ])->and(array_column(CheckoutPaymentAttemptState::cases(), 'value'))->toBe([
        'not_attempted',
        'processing',
        'failed',
        'succeeded',
    ])->and(array_column(CheckoutSessionStatus::cases(), 'value'))->toBe([
        'pending',
        'completed',
        'canceled',
        'expired',
    ])->and(array_column(BillingPortalFlow::cases(), 'value'))->toBe([
        'overview',
        'payment_recovery',
    ])->and(array_column(WebhookEndpointEvent::cases(), 'value'))->toBe([
        'checkout.session.completed',
        'checkout.session.closed',
        'subscription.activated',
        'subscription.renewed',
        'subscription.changed',
        'subscription.canceled',
        'subscription.on_grace_period',
        'subscription.resumed',
        'subscription.ended',
    ]);
});
