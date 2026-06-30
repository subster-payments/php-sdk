<?php

declare(strict_types=1);

use Subster\PhpSdk\Concerns\Collection;
use Subster\PhpSdk\Concerns\Data;
use Subster\PhpSdk\DataObjects\CheckoutSessionTrialData;
use Subster\PhpSdk\DataObjects\CheckoutSessionTrialDurationData;
use Subster\PhpSdk\DataObjects\CreateCheckoutSessionItemData;
use Subster\PhpSdk\DataObjects\CreateCheckoutSessionSubscriptionData;
use Subster\PhpSdk\DataObjects\RecordSubscriptionUsageEventData;

it('serializes nested data objects', function () {
    $trial = CheckoutSessionTrialData::from([
        'amount' => 100,
        'duration' => CheckoutSessionTrialDurationData::from([
            'unit' => 'day',
            'count' => 14,
        ]),
    ]);

    expect($trial->toArray())->toBe([
        'amount' => 100,
        'duration' => [
            'unit' => 'day',
            'count' => 14,
        ],
    ]);
});

it('serializes arrays containing nested data objects recursively', function () {
    $duration = CheckoutSessionTrialDurationData::from([
        'unit' => 'week',
        'count' => 2,
    ]);

    $data = new class([$duration, ['nested' => $duration]]) extends Data
    {
        public function __construct(
            public readonly array $durations,
        ) {}
    };

    expect($data->toArray())->toBe([
        'durations' => [
            [
                'unit' => 'week',
                'count' => 2,
            ],
            [
                'nested' => [
                    'unit' => 'week',
                    'count' => 2,
                ],
            ],
        ],
    ]);
});

it('preserves null values when serializing data objects', function () {
    expect(CreateCheckoutSessionSubscriptionData::from()->toArray())->toBe([
        'trial' => null,
    ]);
});

it('serializes collections containing nested data objects recursively', function () {
    $duration = CheckoutSessionTrialDurationData::from([
        'unit' => 'month',
        'count' => 1,
    ]);

    $collection = Collection::make([
        $duration,
        ['nested' => $duration],
    ]);

    $data = new class($collection) extends Data
    {
        public function __construct(
            public readonly Collection $durations,
        ) {}
    };

    expect($collection->toArray())->toBe([
        [
            'unit' => 'month',
            'count' => 1,
        ],
        [
            'nested' => [
                'unit' => 'month',
                'count' => 1,
            ],
        ],
    ])->and($data->toArray())->toBe([
        'durations' => [
            [
                'unit' => 'month',
                'count' => 1,
            ],
            [
                'nested' => [
                    'unit' => 'month',
                    'count' => 1,
                ],
            ],
        ],
    ]);
});

it('hydrates nested data objects from arrays', function () {
    $trial = CheckoutSessionTrialData::from([
        'amount' => 100,
        'duration' => [
            'unit' => 'day',
            'count' => 14,
        ],
    ]);

    expect($trial->duration)
        ->toBeInstanceOf(CheckoutSessionTrialDurationData::class)
        ->and($trial->toArray())
        ->toBe([
            'amount' => 100,
            'duration' => [
                'unit' => 'day',
                'count' => 14,
            ],
        ]);
});

it('serializes checkout session items with optional quantity', function () {
    expect(CreateCheckoutSessionItemData::from([
        'plan' => 'plan-id',
        'quantity' => 5,
    ])->toArray())->toBe([
        'plan' => 'plan-id',
        'quantity' => 5,
    ]);

    expect(CreateCheckoutSessionItemData::from([
        'plan' => 'plan-id',
    ])->toArray())->toBe([
        'plan' => 'plan-id',
    ]);
});

it('serializes subscription usage events without null optional fields', function () {
    expect(RecordSubscriptionUsageEventData::from([
        'quantity' => 20,
        'occurred_at' => '2026-01-16T10:30:00+00:00',
        'idempotency_key' => 'tenant-users-2026-01-16',
        'metadata' => ['source' => 'tenant-admin'],
    ])->toArray())->toBe([
        'quantity' => 20,
        'occurred_at' => '2026-01-16T10:30:00+00:00',
        'idempotency_key' => 'tenant-users-2026-01-16',
        'metadata' => ['source' => 'tenant-admin'],
    ]);

    expect(RecordSubscriptionUsageEventData::from([
        'quantity' => 20,
    ])->toArray())->toBe([
        'quantity' => 20,
    ]);
});
