<?php

declare(strict_types=1);

use Saloon\Enums\Method;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Subster\PhpSdk\DataObjects\CreateInvoiceRefundData;
use Subster\PhpSdk\DataObjects\RefundData;
use Subster\PhpSdk\Enums\Currency;
use Subster\PhpSdk\Enums\RefundStatus;
use Subster\PhpSdk\Requests\CreateInvoiceRefundRequest;
use Subster\PhpSdk\SubsterConnector;

it('accepts a snake case refund idempotency key from raw data', function () {
    $mockClient = new MockClient([
        CreateInvoiceRefundRequest::class => MockResponse::make(refundResponse(), 201),
    ]);
    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    $connector->invoices()->refund('invoice-id', CreateInvoiceRefundData::from([
        'amount' => 500,
        'reason' => 'Customer request',
        'idempotency_key' => 'refund-operation-id',
    ]));

    $mockClient->assertSent(fn (Request $request): bool => $request instanceof CreateInvoiceRefundRequest
        && $request->getMethod() === Method::POST
        && $request->headers()->get('Idempotency-Key') === 'refund-operation-id'
        && ! array_key_exists('idempotency_key', $request->body()->all()));
});

it('sends invoice refund data with idempotency only in the header', function () {
    $mockClient = new MockClient([
        CreateInvoiceRefundRequest::class => MockResponse::make(refundResponse(), 201),
    ]);
    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    $connector->invoices()->refund('invoice-id', new CreateInvoiceRefundData(
        amount: 500,
        reason: 'Customer request',
        idempotencyKey: 'refund-operation-id',
    ));

    $mockClient->assertSent(fn (Request $request): bool => $request instanceof CreateInvoiceRefundRequest
        && $request->resolveEndpoint() === 'invoices/invoice-id/refunds'
        && $request->body()->all() === [
            'amount' => 500.0,
            'reason' => 'Customer request',
        ]
        && $request->headers()->get('Idempotency-Key') === 'refund-operation-id'
        && ! array_key_exists('idempotency_key', $request->body()->all()));
});

it('omits amount to request the full refundable balance', function () {
    $mockClient = new MockClient([
        CreateInvoiceRefundRequest::class => MockResponse::make(refundResponse(), 201),
    ]);
    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    $connector->invoices()->refund('invoice-id', new CreateInvoiceRefundData(
        reason: 'Full refund',
        idempotencyKey: 'full-refund-operation-id',
    ));

    $mockClient->assertSent(fn (Request $request): bool => $request instanceof CreateInvoiceRefundRequest
        && $request->body()->all() === ['reason' => 'Full refund']);
});

it('hydrates refund responses for created replayed and pending requests', function (int $status, string $refundStatus) {
    $response = refundResponse($refundStatus);
    $mockClient = new MockClient([
        CreateInvoiceRefundRequest::class => MockResponse::make($response, $status),
    ]);
    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    $refund = $connector->invoices()->refund('invoice-id', new CreateInvoiceRefundData(
        amount: 500,
        reason: 'Customer request',
        idempotencyKey: 'refund-operation-id',
    ));

    expect($refund)->toBeInstanceOf(RefundData::class)
        ->and($refund->id)->toBe('refund-id')
        ->and($refund->status)->toBe(RefundStatus::from($refundStatus))
        ->and($refund->source)->toBe('api')
        ->and($refund->invoice)->toBe('invoice-id')
        ->and($refund->charge)->toBe('charge-id')
        ->and($refund->amount)->toBe(500.0)
        ->and($refund->currency)->toBe(Currency::RUB)
        ->and($refund->idempotency_key)->toBe('refund-operation-id')
        ->and($refund->provider_reference)->toBe($refundStatus === 'pending' ? null : 'provider-refund-id')
        ->and($refund->refunded_at?->format(DateTimeInterface::ATOM))->toBe(
            $refundStatus === 'pending' ? null : '2026-08-01T12:00:00+00:00'
        )
        ->and($refund->created_at)->toBeInstanceOf(DateTimeImmutable::class)
        ->and($refund->created_at->format(DateTimeInterface::ATOM))->toBe('2026-08-01T11:59:00+00:00')
        ->and($refund->updated_at)->toBeInstanceOf(DateTimeImmutable::class)
        ->and($refund->updated_at->format(DateTimeInterface::ATOM))->toBe('2026-08-01T12:00:00+00:00');
})->with([
    'created' => [201, 'succeeded'],
    'replayed' => [200, 'succeeded'],
    'pending' => [202, 'pending'],
]);

it('throws the Saloon API exception for refund errors', function (int $status) {
    $mockClient = new MockClient([
        CreateInvoiceRefundRequest::class => MockResponse::make([
            'message' => 'The refund amount exceeds the refundable balance.',
        ], $status),
    ]);
    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    expect(fn () => $connector->invoices()->refund('invoice-id', new CreateInvoiceRefundData(
        amount: 500,
        reason: 'Customer request',
        idempotencyKey: 'refund-operation-id',
    )))->toThrow(RequestException::class);
})->with([409, 422]);

/**
 * @return array<string, mixed>
 */
function refundResponse(string $status = 'succeeded'): array
{
    return [
        'id' => 'refund-id',
        'status' => $status,
        'source' => 'api',
        'invoice' => 'invoice-id',
        'charge' => 'charge-id',
        'amount' => 500,
        'currency' => 'RUB',
        'reason' => 'Customer request',
        'idempotency_key' => 'refund-operation-id',
        'provider_reference' => $status === 'pending' ? null : 'provider-refund-id',
        'failure_message' => null,
        'refunded_at' => $status === 'pending' ? null : '2026-08-01T12:00:00+00:00',
        'created_at' => '2026-08-01T11:59:00+00:00',
        'updated_at' => '2026-08-01T12:00:00+00:00',
    ];
}
