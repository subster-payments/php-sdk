<?php

declare(strict_types=1);

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Subster\PhpSdk\DataObjects\CreateBillingPortalSessionData;
use Subster\PhpSdk\Enums\BillingPortalFlow;
use Subster\PhpSdk\Requests\CreateBillingPortalSessionRequest;
use Subster\PhpSdk\SubsterConnector;

it('keeps the legacy billing portal request body unchanged', function (): void {
    $mockClient = new MockClient([
        CreateBillingPortalSessionRequest::class => MockResponse::make([
            'id' => 'portal-session-id',
            'url' => 'https://subster.test/billing-portal/portal-session-id',
        ]),
    ]);

    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    $connector->billingPortalSessions()->create(new CreateBillingPortalSessionData(
        customer: 'customer-id',
        return_url: 'https://example.ru/billing',
    ));

    $mockClient->assertSent(fn (Request $request): bool => $request instanceof CreateBillingPortalSessionRequest
        && $request->body()->all() === [
            'customer' => 'customer-id',
            'return_url' => 'https://example.ru/billing',
        ]);
});

it('creates a direct payment recovery billing portal session', function (): void {
    $mockClient = new MockClient([
        CreateBillingPortalSessionRequest::class => MockResponse::make([
            'id' => 'portal-session-id',
            'url' => 'https://subster.test/billing-portal/portal-session-id/add-payment-method',
        ]),
    ]);

    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    $session = $connector->billingPortalSessions()->create(CreateBillingPortalSessionData::from([
        'customer' => 'customer-id',
        'return_url' => 'https://example.ru/billing/recovered',
        'flow' => BillingPortalFlow::PaymentRecovery,
    ]));

    expect($session->url)->toBe('https://subster.test/billing-portal/portal-session-id/add-payment-method');

    $mockClient->assertSent(fn (Request $request): bool => $request instanceof CreateBillingPortalSessionRequest
        && $request->body()->all() === [
            'customer' => 'customer-id',
            'return_url' => 'https://example.ru/billing/recovered',
            'flow' => BillingPortalFlow::PaymentRecovery->value,
        ]);
});
