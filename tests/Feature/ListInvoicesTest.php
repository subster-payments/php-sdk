<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Subster\PhpSdk\Concerns\Collection;
use Subster\PhpSdk\DataObjects\InvoiceCustomerData;
use Subster\PhpSdk\DataObjects\InvoiceData;
use Subster\PhpSdk\DataObjects\InvoiceDiscountCouponData;
use Subster\PhpSdk\DataObjects\InvoiceDiscountData;
use Subster\PhpSdk\DataObjects\InvoiceDiscountPromotionCodeData;
use Subster\PhpSdk\DataObjects\InvoiceItemData;
use Subster\PhpSdk\DataObjects\InvoiceListData;
use Subster\PhpSdk\DataObjects\InvoiceSubscriptionData;
use Subster\PhpSdk\DataObjects\ListInvoicesData;
use Subster\PhpSdk\Enums\CouponDiscountType;
use Subster\PhpSdk\Enums\CouponDuration;
use Subster\PhpSdk\Enums\Currency;
use Subster\PhpSdk\Enums\InvoiceStatus;
use Subster\PhpSdk\Enums\PlanType;
use Subster\PhpSdk\Enums\PricingModel;
use Subster\PhpSdk\Enums\SubscriptionStatus;
use Subster\PhpSdk\Requests\ListInvoicesRequest;
use Subster\PhpSdk\SubsterConnector;

it('sends list invoices request', function () {
    $mockClient = new MockClient([
        ListInvoicesRequest::class => MockResponse::make(invoiceListResponse()),
    ]);

    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    $connector->invoices()->all();

    $mockClient->assertSent(fn (Request $request): bool => $request instanceof ListInvoicesRequest
        && $request->resolveEndpoint() === 'invoices'
        && $request->query()->all() === []);
});

it('sends list invoice filters as query parameters', function () {
    $mockClient = new MockClient([
        ListInvoicesRequest::class => MockResponse::make(invoiceListResponse()),
    ]);

    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    $connector->invoices()->all(ListInvoicesData::from([
        'limit' => 25,
        'starting_after' => 'invoice-starting-after-id',
        'customer' => 'customer-id',
        'subscription' => 'subscription-id',
        'paid_at_gte' => CarbonImmutable::parse('2026-01-01T03:00:00+03:00'),
        'paid_at_lte' => '2026-01-31',
    ]));

    $mockClient->assertSent(fn (Request $request): bool => $request instanceof ListInvoicesRequest
        && $request->query()->all() === [
            'limit' => 25,
            'starting_after' => 'invoice-starting-after-id',
            'customer' => 'customer-id',
            'subscription' => 'subscription-id',
            'paid_at[gte]' => '2026-01-01T00:00:00+00:00',
            'paid_at[lte]' => '2026-01-31',
        ]);
});

it('sends ending before invoice cursor as a query parameter', function () {
    $mockClient = new MockClient([
        ListInvoicesRequest::class => MockResponse::make(invoiceListResponse()),
    ]);

    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    $connector->invoices()->all(ListInvoicesData::from([
        'ending_before' => 'invoice-ending-before-id',
    ]));

    $mockClient->assertSent(fn (Request $request): bool => $request instanceof ListInvoicesRequest
        && $request->query()->all() === [
            'ending_before' => 'invoice-ending-before-id',
        ]);
});

it('returns invoice list data from a mocked Saloon response', function () {
    $mockClient = new MockClient([
        ListInvoicesRequest::class => MockResponse::make(invoiceListResponse()),
    ]);

    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    $invoices = $connector->invoices()->all();
    $subscriptionInvoice = $invoices->data->items[0];
    $oneTimeInvoice = $invoices->data->items[1];
    $subscriptionItem = $subscriptionInvoice->items->items[0];
    $oneTimeItem = $oneTimeInvoice->items->items[0];
    $discount = $subscriptionInvoice->discount;

    expect($invoices)
        ->toBeInstanceOf(InvoiceListData::class)
        ->and($invoices->object)->toBe('list')
        ->and($invoices->url)->toBe('/api/v1/invoices')
        ->and($invoices->has_more)->toBeTrue()
        ->and($invoices->data)->toBeInstanceOf(Collection::class)
        ->and($invoices->data->items)->toHaveCount(2)
        ->and($subscriptionInvoice)->toBeInstanceOf(InvoiceData::class)
        ->and($subscriptionInvoice->id)->toBe('invoice-subscription-id')
        ->and($subscriptionInvoice->status)->toBe(InvoiceStatus::Paid)
        ->and($subscriptionInvoice->amount)->toBe(1800.0)
        ->and($subscriptionInvoice->subtotal_amount)->toBe(2000.0)
        ->and($subscriptionInvoice->discount_amount)->toBe(200.0)
        ->and($subscriptionInvoice->currency)->toBe(Currency::RUB)
        ->and($subscriptionInvoice->description)->toBe('Subscription renewal')
        ->and($subscriptionInvoice->paid_at)->toBeInstanceOf(CarbonImmutable::class)
        ->and($subscriptionInvoice->paid_at->toIso8601String())->toBe('2026-01-16T10:30:00+00:00')
        ->and($subscriptionInvoice->created_at->timestamp)->toBe(1768559400)
        ->and($subscriptionInvoice->updated_at->timestamp)->toBe(1768559500)
        ->and($subscriptionInvoice->customer)->toBeInstanceOf(InvoiceCustomerData::class)
        ->and($subscriptionInvoice->customer->id)->toBe('customer-id')
        ->and($subscriptionInvoice->customer->name)->toBeNull()
        ->and($subscriptionInvoice->customer->email)->toBe('customer@example.com')
        ->and($subscriptionInvoice->customer->created_at->timestamp)->toBe(1768473000)
        ->and($subscriptionInvoice->customer->updated_at->timestamp)->toBe(1768559400)
        ->and($subscriptionInvoice->subscription)->toBeInstanceOf(InvoiceSubscriptionData::class)
        ->and($subscriptionInvoice->subscription->id)->toBe('subscription-id')
        ->and($subscriptionInvoice->subscription->status)->toBe(SubscriptionStatus::Active)
        ->and($subscriptionInvoice->subscription->plan)->toBe('plan-recurring-id')
        ->and($subscriptionInvoice->subscription->quantity)->toBe(2)
        ->and($subscriptionInvoice->subscription->starts_at->toIso8601String())->toBe('2026-01-16T00:00:00+00:00')
        ->and($subscriptionInvoice->subscription->ends_at->toIso8601String())->toBe('2026-02-16T00:00:00+00:00')
        ->and($subscriptionInvoice->subscription->cancel_at_period_end)->toBeFalse()
        ->and($discount)->toBeInstanceOf(InvoiceDiscountData::class)
        ->and($discount->subtotal_amount)->toBe(2000.0)
        ->and($discount->discount_amount)->toBe(200.0)
        ->and($discount->total_amount)->toBe(1800.0)
        ->and($discount->currency)->toBe(Currency::RUB)
        ->and($discount->coupon)->toBeInstanceOf(InvoiceDiscountCouponData::class)
        ->and($discount->coupon->id)->toBe('coupon-id')
        ->and($discount->coupon->api_identifier)->toBe('launch_coupon')
        ->and($discount->coupon->name)->toBe('Launch')
        ->and($discount->coupon->discount_type)->toBe(CouponDiscountType::FixedAmount)
        ->and($discount->coupon->percent_off)->toBeNull()
        ->and($discount->coupon->amount_off)->toBe(200.0)
        ->and($discount->coupon->duration)->toBe(CouponDuration::Forever)
        ->and($discount->coupon->duration_in_months)->toBeNull()
        ->and($discount->promotion_code)->toBeInstanceOf(InvoiceDiscountPromotionCodeData::class)
        ->and($discount->promotion_code->id)->toBe('promotion-code-id')
        ->and($discount->promotion_code->code)->toBe('LAUNCH200')
        ->and($subscriptionItem)->toBeInstanceOf(InvoiceItemData::class)
        ->and($subscriptionItem->id)->toBe('invoice-item-subscription-id')
        ->and($subscriptionItem->plan)->toBe('plan-recurring-id')
        ->and($subscriptionItem->product_name)->toBe('Pro')
        ->and($subscriptionItem->type)->toBe(PlanType::Recurring)
        ->and($subscriptionItem->pricing_model)->toBe(PricingModel::UsageBased)
        ->and($subscriptionItem->unit_amount)->toBe(1000.0)
        ->and($subscriptionItem->quantity)->toBe(2)
        ->and($subscriptionItem->amount)->toBe(1800.0)
        ->and($subscriptionItem->description)->toBe('Two seats')
        ->and($subscriptionItem->metadata)->toBe(['seats' => 2])
        ->and($oneTimeInvoice->status)->toBe(InvoiceStatus::Paid)
        ->and($oneTimeInvoice->currency)->toBe(Currency::RUB)
        ->and($oneTimeInvoice->subtotal_amount)->toBe(400.0)
        ->and($oneTimeInvoice->discount_amount)->toBe(0.0)
        ->and($oneTimeInvoice->discount)->toBeNull()
        ->and($oneTimeInvoice->subscription)->toBeNull()
        ->and($oneTimeInvoice->description)->toBeNull()
        ->and($oneTimeItem->plan)->toBeNull()
        ->and($oneTimeItem->type)->toBe(PlanType::OneTime)
        ->and($oneTimeItem->pricing_model)->toBeNull()
        ->and($oneTimeItem->description)->toBeNull()
        ->and($oneTimeItem->metadata)->toBeNull();

    $mockClient->assertSentCount(1, ListInvoicesRequest::class);
});

it('falls back when invoice response does not contain discount fields', function () {
    $response = invoiceListResponse();

    unset(
        $response['data'][0]['subtotal_amount'],
        $response['data'][0]['discount_amount'],
        $response['data'][0]['discount'],
    );

    $mockClient = new MockClient([
        ListInvoicesRequest::class => MockResponse::make($response),
    ]);

    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    $invoice = $connector->invoices()->all()->data->items[0];

    expect($invoice)
        ->toBeInstanceOf(InvoiceData::class)
        ->amount->toBe(1800.0)
        ->subtotal_amount->toBe(1800.0)
        ->discount_amount->toBe(0.0)
        ->discount->toBeNull();
});

it('hydrates legacy invoice coupon api id into api identifier', function () {
    $response = invoiceListResponse();
    $coupon = $response['data'][0]['discount']['coupon'];

    unset($coupon['api_identifier']);

    $coupon['api_id'] = 'legacy_coupon';
    $response['data'][0]['discount']['coupon'] = $coupon;

    $mockClient = new MockClient([
        ListInvoicesRequest::class => MockResponse::make($response),
    ]);

    $connector = new SubsterConnector('test-token', 'https://subster.test/api/v1/');
    $connector->withMockClient($mockClient);

    expect($connector->invoices()->all()->data->items[0]->discount->coupon->api_identifier)
        ->toBe('legacy_coupon');
});

function invoiceListResponse(): array
{
    return [
        'object' => 'list',
        'url' => '/api/v1/invoices',
        'has_more' => true,
        'data' => [
            [
                'id' => 'invoice-subscription-id',
                'status' => 'paid',
                'amount' => 1800,
                'subtotal_amount' => 2000,
                'discount_amount' => 200,
                'discount' => [
                    'coupon' => [
                        'id' => 'coupon-id',
                        'api_identifier' => 'launch_coupon',
                        'name' => 'Launch',
                        'discount_type' => 'fixed_amount',
                        'percent_off' => null,
                        'amount_off' => 200,
                        'duration' => 'forever',
                        'duration_in_months' => null,
                    ],
                    'promotion_code' => [
                        'id' => 'promotion-code-id',
                        'code' => 'LAUNCH200',
                    ],
                    'subtotal_amount' => 2000,
                    'discount_amount' => 200,
                    'total_amount' => 1800,
                    'currency' => 'RUB',
                ],
                'currency' => 'RUB',
                'description' => 'Subscription renewal',
                'paid_at' => '2026-01-16T10:30:00+00:00',
                'created_at' => 1768559400,
                'updated_at' => 1768559500,
                'customer' => [
                    'id' => 'customer-id',
                    'name' => null,
                    'email' => 'customer@example.com',
                    'created_at' => 1768473000,
                    'updated_at' => 1768559400,
                ],
                'subscription' => [
                    'id' => 'subscription-id',
                    'status' => 'active',
                    'plan' => 'plan-recurring-id',
                    'quantity' => 2,
                    'starts_at' => '2026-01-16T00:00:00+00:00',
                    'ends_at' => '2026-02-16T00:00:00+00:00',
                    'cancel_at_period_end' => false,
                ],
                'items' => [
                    [
                        'id' => 'invoice-item-subscription-id',
                        'plan' => 'plan-recurring-id',
                        'product_name' => 'Pro',
                        'type' => 'recurring',
                        'pricing_model' => 'usage_based',
                        'unit_amount' => 1000,
                        'quantity' => 2,
                        'amount' => 1800,
                        'description' => 'Two seats',
                        'metadata' => ['seats' => 2],
                    ],
                ],
            ],
            [
                'id' => 'invoice-one-time-id',
                'status' => 'paid',
                'amount' => 400,
                'subtotal_amount' => 400,
                'discount_amount' => 0,
                'discount' => null,
                'currency' => 'RUB',
                'description' => null,
                'paid_at' => '2026-01-17T11:00:00+00:00',
                'created_at' => 1768647600,
                'updated_at' => 1768647700,
                'customer' => [
                    'id' => 'customer-id',
                    'name' => 'Acme',
                    'email' => 'customer@example.com',
                    'created_at' => 1768473000,
                    'updated_at' => 1768559400,
                ],
                'subscription' => null,
                'items' => [
                    [
                        'id' => 'invoice-item-one-time-id',
                        'plan' => null,
                        'product_name' => 'Tokens',
                        'type' => 'one_time',
                        'unit_amount' => 80,
                        'quantity' => 5,
                        'amount' => 400,
                        'description' => null,
                        'metadata' => null,
                    ],
                ],
            ],
        ],
    ];
}
