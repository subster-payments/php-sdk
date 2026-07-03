<?php

declare(strict_types=1);

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Subster\PhpSdk\DataObjects\CreateCustomerData;
use Subster\PhpSdk\DataObjects\CustomerData;
use Subster\PhpSdk\DataObjects\UpdateCustomerData;
use Subster\PhpSdk\Requests\CreateCustomerRequest;
use Subster\PhpSdk\Requests\UpdateCustomerRequest;
use Subster\PhpSdk\SubsterConnector;

it('omits null customer create name and hydrates nullable names', function () {
    $mockClient = new MockClient([
        CreateCustomerRequest::class => MockResponse::make(customerResponse(['name' => null])),
    ]);

    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    $customer = $connector->customers()->create(CreateCustomerData::from([
        'email' => 'billing@example.com',
    ]));

    expect($customer)
        ->toBeInstanceOf(CustomerData::class)
        ->and($customer->name)->toBeNull();

    $mockClient->assertSent(fn (Request $request): bool => $request instanceof CreateCustomerRequest
        && $request->resolveEndpoint() === 'customers'
        && $request->body()->all() === [
            'email' => 'billing@example.com',
        ]);
});

it('sends customer update request body', function () {
    $mockClient = new MockClient([
        UpdateCustomerRequest::class => MockResponse::make(customerResponse()),
    ]);

    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    $connector->customers()->update(UpdateCustomerData::from([
        'id' => 'customer-id',
        'name' => 'Acme',
        'email' => 'billing@example.com',
    ]));

    $mockClient->assertSent(fn (Request $request): bool => $request instanceof UpdateCustomerRequest
        && $request->resolveEndpoint() === 'customers/customer-id'
        && $request->body()->all() === [
            'name' => 'Acme',
            'email' => 'billing@example.com',
        ]);
});

it('omits null customer update values', function () {
    $mockClient = new MockClient([
        UpdateCustomerRequest::class => MockResponse::make(customerResponse()),
    ]);

    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    $connector->customers()->update(UpdateCustomerData::from([
        'id' => 'customer-id',
        'name' => 'Acme',
    ]));

    $mockClient->assertSent(fn (Request $request): bool => $request instanceof UpdateCustomerRequest
        && $request->body()->all() === [
            'name' => 'Acme',
        ]);
});

it('returns customer data from a mocked update response', function () {
    $mockClient = new MockClient([
        UpdateCustomerRequest::class => MockResponse::make(customerResponse()),
    ]);

    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    $customer = $connector->customers()->update(UpdateCustomerData::from([
        'id' => 'customer-id',
        'name' => 'Acme',
    ]));

    expect($customer)
        ->toBeInstanceOf(CustomerData::class)
        ->and($customer->id)->toBe('customer-id')
        ->and($customer->name)->toBe('Acme')
        ->and($customer->email)->toBe('billing@example.com')
        ->and($customer->created_at->toIso8601String())->toBe('2026-01-15T10:30:00+00:00')
        ->and($customer->updated_at->toIso8601String())->toBe('2026-01-16T10:30:00+00:00');

    $mockClient->assertSentCount(1, UpdateCustomerRequest::class);
});

function customerResponse(array $overrides = []): array
{
    return [
        ...[
            'id' => 'customer-id',
            'name' => 'Acme',
            'email' => 'billing@example.com',
            'created_at' => '2026-01-15T10:30:00+00:00',
            'updated_at' => '2026-01-16T10:30:00+00:00',
        ],
        ...$overrides,
    ];
}
