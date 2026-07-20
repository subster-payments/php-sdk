<?php

declare(strict_types=1);

use Saloon\Enums\Method;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Subster\PhpSdk\DataObjects\CheckoutSessionStatusData;
use Subster\PhpSdk\Enums\CheckoutPaymentAttemptState;
use Subster\PhpSdk\Enums\CheckoutPaymentState;
use Subster\PhpSdk\Enums\CheckoutSessionStatus;
use Subster\PhpSdk\Enums\WebhookEndpointEvent;
use Subster\PhpSdk\Requests\CancelCheckoutSessionRequest;
use Subster\PhpSdk\SubsterConnector;

it('sends the exact checkout session cancellation request', function (): void {
    $mockClient = new MockClient([
        CancelCheckoutSessionRequest::class => MockResponse::make(canceledCheckoutResponse()),
    ]);
    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    $connector->checkoutSessions()->cancel('checkout-session-id');

    $mockClient->assertSent(fn (Request $request): bool => $request instanceof CancelCheckoutSessionRequest
        && $request->getMethod() === Method::DELETE
        && $request->resolveEndpoint() === 'checkout/session/checkout-session-id');
});

it('hydrates a canceled checkout status response', function (): void {
    $mockClient = new MockClient([
        CancelCheckoutSessionRequest::class => MockResponse::make(canceledCheckoutResponse()),
    ]);
    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    $session = $connector->checkoutSessions()->cancel('checkout-session-id');

    expect($session)
        ->toBeInstanceOf(CheckoutSessionStatusData::class)
        ->id->toBe('checkout-session-id')
        ->status->toBe(CheckoutSessionStatus::Canceled)
        ->payment_state->toBe(CheckoutPaymentState::RequiresPayment)
        ->payment_attempt_state->toBe(CheckoutPaymentAttemptState::NotAttempted)
        ->checkout_url->toBeNull()
        ->event->toBe(WebhookEndpointEvent::CheckoutSessionClosed)
        ->and($session->data)->toBe([
            'checkout_session' => 'checkout-session-id',
            'status' => 'canceled',
        ]);
});

it('surfaces checkout cancellation API conflicts', function (): void {
    $mockClient = new MockClient([
        CancelCheckoutSessionRequest::class => MockResponse::make(
            ['message' => 'Checkout payment result is still processing.'],
            409,
        ),
    ]);
    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    expect(fn (): CheckoutSessionStatusData => $connector
        ->checkoutSessions()
        ->cancel('checkout-session-id'))
        ->toThrow(RequestException::class);
});

/**
 * @return array<string, mixed>
 */
function canceledCheckoutResponse(): array
{
    return [
        'id' => 'checkout-session-id',
        'status' => 'canceled',
        'payment_state' => 'requires_payment',
        'payment_attempt_state' => 'not_attempted',
        'checkout_url' => null,
        'amount' => 99,
        'currency' => 'RUB',
        'event' => 'checkout.session.closed',
        'data' => [
            'checkout_session' => 'checkout-session-id',
            'status' => 'canceled',
        ],
    ];
}
