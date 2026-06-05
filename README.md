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
