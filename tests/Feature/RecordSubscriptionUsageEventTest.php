<?php

declare(strict_types=1);

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Subster\PhpSdk\DataObjects\RecordSubscriptionUsageEventData;
use Subster\PhpSdk\DataObjects\SubscriptionUsageEventData;
use Subster\PhpSdk\Requests\RecordSubscriptionUsageEventRequest;
use Subster\PhpSdk\SubsterConnector;

it('sends subscription usage event request body', function () {
    $mockClient = new MockClient([
        RecordSubscriptionUsageEventRequest::class => MockResponse::make(subscriptionUsageEventResponse()),
    ]);

    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    $connector->subscriptions()->recordUsage('subscription-id', RecordSubscriptionUsageEventData::from([
        'quantity' => 20,
        'occurred_at' => '2026-01-16T10:30:00+00:00',
        'idempotency_key' => 'tenant-users-2026-01-16',
        'metadata' => ['source' => 'tenant-admin'],
    ]));

    $mockClient->assertSent(fn (Request $request): bool => $request instanceof RecordSubscriptionUsageEventRequest
        && $request->resolveEndpoint() === 'subscriptions/subscription-id/usage-events'
        && $request->body()->all() === [
            'quantity' => 20,
            'occurred_at' => '2026-01-16T10:30:00+00:00',
            'idempotency_key' => 'tenant-users-2026-01-16',
            'metadata' => ['source' => 'tenant-admin'],
        ]);
});

it('omits optional subscription usage event fields', function () {
    $mockClient = new MockClient([
        RecordSubscriptionUsageEventRequest::class => MockResponse::make(subscriptionUsageEventResponse([
            'occurred_at' => '2026-01-16T10:30:00+00:00',
            'idempotency_key' => null,
            'metadata' => null,
        ])),
    ]);

    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    $connector->subscriptions()->recordUsage('subscription-id', RecordSubscriptionUsageEventData::from([
        'quantity' => 20,
    ]));

    $mockClient->assertSent(fn (Request $request): bool => $request instanceof RecordSubscriptionUsageEventRequest
        && $request->body()->all() === [
            'quantity' => 20,
        ]);
});

it('returns subscription usage event data from a mocked Saloon response', function () {
    $mockClient = new MockClient([
        RecordSubscriptionUsageEventRequest::class => MockResponse::make(subscriptionUsageEventResponse()),
    ]);

    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    $event = $connector->subscriptions()->recordUsage('subscription-id', RecordSubscriptionUsageEventData::from([
        'quantity' => 20,
        'occurred_at' => '2026-01-16T10:30:00+00:00',
        'idempotency_key' => 'tenant-users-2026-01-16',
        'metadata' => ['source' => 'tenant-admin'],
    ]));

    expect($event)
        ->toBeInstanceOf(SubscriptionUsageEventData::class)
        ->and($event->id)->toBe('usage-event-id')
        ->and($event->subscription)->toBe('subscription-id')
        ->and($event->customer)->toBe('customer-id')
        ->and($event->plan)->toBe('plan-id')
        ->and($event->quantity)->toBe(20)
        ->and($event->occurred_at)->toBeInstanceOf(DateTimeImmutable::class)
        ->and($event->occurred_at->format(DateTimeInterface::ATOM))->toBe('2026-01-16T10:30:00+00:00')
        ->and($event->idempotency_key)->toBe('tenant-users-2026-01-16')
        ->and($event->metadata)->toBe(['source' => 'tenant-admin']);

    $mockClient->assertSentCount(1, RecordSubscriptionUsageEventRequest::class);
});

it('serializes subscription usage event occurred at date time as utc iso string', function () {
    $mockClient = new MockClient([
        RecordSubscriptionUsageEventRequest::class => MockResponse::make(subscriptionUsageEventResponse()),
    ]);

    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    $connector->subscriptions()->recordUsage('subscription-id', RecordSubscriptionUsageEventData::from([
        'quantity' => 20,
        'occurred_at' => new DateTimeImmutable('2026-01-16T13:30:00+03:00'),
    ]));

    $mockClient->assertSent(fn (Request $request): bool => $request instanceof RecordSubscriptionUsageEventRequest
        && $request->body()->all() === [
            'quantity' => 20,
            'occurred_at' => '2026-01-16T10:30:00+00:00',
        ]);
});

function subscriptionUsageEventResponse(array $overrides = []): array
{
    return [
        ...[
            'id' => 'usage-event-id',
            'subscription' => 'subscription-id',
            'customer' => 'customer-id',
            'plan' => 'plan-id',
            'quantity' => 20,
            'occurred_at' => '2026-01-16T10:30:00+00:00',
            'idempotency_key' => 'tenant-users-2026-01-16',
            'metadata' => ['source' => 'tenant-admin'],
        ],
        ...$overrides,
    ];
}
