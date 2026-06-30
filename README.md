# Subster PHP SDK

Work in progress. Not ready for production yet!

## Requirements
- PHP 8.1 or later.

## Installation
Install via composer:
```bash
composer require subster/php-sdk
```

# Usage

## Create a checkout session

```php
use Subster\PhpSdk\SubsterConnector;
use Subster\PhpSdk\DataObjects\CreateCheckoutSessionData;

$subster = new SubsterConnector('your-api-key');

$session = $subster->checkoutSessions()->create(
    CreateCheckoutSessionData::from([
        'customer' => 'customer-id',
        'items' => [
            [
                'plan' => 'your-plan-id',
            ],
        ],
        'success_url' => 'https://example.ru/success',
        'cancel_url' => 'https://example.ru/cancel',
    ])
);
```

## Create a one-time checkout session with quantity

Use `quantity` when the customer buys multiple units of the same price, for example 5 token packs. If `quantity` is omitted, Subster treats the checkout item as quantity `1`.

```php
use Subster\PhpSdk\SubsterConnector;
use Subster\PhpSdk\DataObjects\CreateCheckoutSessionData;
use Subster\PhpSdk\DataObjects\CreateCheckoutSessionItemData;

$subster = new SubsterConnector('your-api-key');

$session = $subster->checkoutSessions()->create(
    CreateCheckoutSessionData::from([
        'customer' => 'customer-id',
        'items' => [
            CreateCheckoutSessionItemData::from([
                'plan' => 'your-one-time-plan-id',
                'quantity' => 5,
            ]),
        ],
        'success_url' => 'https://example.ru/success',
        'cancel_url' => 'https://example.ru/cancel',
    ])
);
```

Raw arrays are still supported:

```php
'items' => [
    [
        'plan' => 'your-one-time-plan-id',
        'quantity' => 5,
    ],
],
```

## Create a checkout session with a paid trial

```php
use Subster\PhpSdk\SubsterConnector;
use Subster\PhpSdk\DataObjects\CheckoutSessionTrialData;
use Subster\PhpSdk\DataObjects\CheckoutSessionTrialDurationData;
use Subster\PhpSdk\DataObjects\CreateCheckoutSessionData;
use Subster\PhpSdk\DataObjects\CreateCheckoutSessionSubscriptionData;

$subster = new SubsterConnector('your-api-key');

$session = $subster->checkoutSessions()->create(
    CreateCheckoutSessionData::from([
        'customer' => 'customer-id',
        'items' => [
            [
                'plan' => 'your-recurring-plan-id',
            ],
        ],
        'subscription_data' => CreateCheckoutSessionSubscriptionData::from([
            'trial' => CheckoutSessionTrialData::from([
                'amount' => 100,
                'duration' => CheckoutSessionTrialDurationData::from([
                    'unit' => 'day',
                    'count' => 14,
                ]),
            ]),
        ]),
        'success_url' => 'https://example.ru/success',
        'cancel_url' => 'https://example.ru/cancel',
    ])
);
```

Allowed trial duration units: `hour`, `day`, `week`, `month`, `year`.

## Create a billing portal session

```php
use Subster\PhpSdk\SubsterConnector;
use Subster\PhpSdk\DataObjects\CreateBillingPortalSessionData;

$subster = new SubsterConnector('your-api-key');

$session = $subster->billingPortalSessions()->create(
    CreateBillingPortalSessionData::from([
        'customer' => 'customer-id',
        'return_url' => 'https://example.ru/billing',
    ])
);
```

## Change a subscription plan

```php
use Subster\PhpSdk\SubsterConnector;
use Subster\PhpSdk\DataObjects\ChangeSubscriptionPlanData;

$subster = new SubsterConnector('your-api-key');

$change = $subster->subscriptions()->changePlan(
    'subscription-id',
    ChangeSubscriptionPlanData::from([
        'plan' => 'target-plan-id',
        'success_url' => 'https://example.ru/success',
        'cancel_url' => 'https://example.ru/cancel',
    ])
);

if ($change->mode === 'checkout') {
    // Redirect the customer to $change->url.
}
```

## List paid invoices

```php
use Subster\PhpSdk\SubsterConnector;
use Subster\PhpSdk\DataObjects\ListInvoicesData;

$subster = new SubsterConnector('your-api-key');

$invoices = $subster->invoices()->all(ListInvoicesData::from([
    'customer' => 'customer-id',
    'paid_at_gte' => '2026-01-01',
    'paid_at_lte' => '2026-01-31',
    'limit' => 10,
]));

foreach ($invoices->data as $invoice) {
    // $invoice->customer, $invoice->subscription, and $invoice->items are included.
}
```

If `$invoices->has_more` is true, request the next page with the last invoice id:

```php
$nextPage = $subster->invoices()->all(ListInvoicesData::from([
    'starting_after' => $invoices->data->items[array_key_last($invoices->data->items)]->id,
]));
```

# Testing
```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Subster](https://github.com/subster)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
