<?php

declare(strict_types=1);

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Subster\PhpSdk\Requests\GetCheckoutSessionRequest;
use Subster\PhpSdk\SubsterConnector;

it('preserves connector defaults and custom base urls', function (): void {
    $connector = new SubsterConnector('test-token');
    $customConnector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');

    expect($connector->resolveBaseUrl())->toBe('https://subster.ru/api/v1/')
        ->and($connector->config()->all())->toBe(['timeout' => 300])
        ->and($customConnector->resolveBaseUrl())->toBe('https://subster.test/api/v1/');
});

it('preserves bearer token authentication', function (): void {
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

    $pendingRequest = $mockClient->getLastPendingRequest();

    expect($pendingRequest)->not->toBeNull()
        ->and($pendingRequest->headers()->get('Authorization'))->toBe('Bearer test-token')
        ->and($pendingRequest->getUrl())->toBe('https://subster.test/api/v1/checkout/session/checkout-session-id');
});
