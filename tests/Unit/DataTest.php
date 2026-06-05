<?php

declare(strict_types=1);

use Subster\PhpSdk\Concerns\Data;
use Subster\PhpSdk\DataObjects\CheckoutSessionTrialData;
use Subster\PhpSdk\DataObjects\CheckoutSessionTrialDurationData;
use Subster\PhpSdk\DataObjects\CreateCheckoutSessionSubscriptionData;

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
