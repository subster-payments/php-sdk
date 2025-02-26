# Subster PHP SDK

Work in progress. Not ready for production yet!

## Requirements
- PHP 8.1 or later.

## Installation
Install via composer:
```bash
composer require subster/php-sdk
````

# Usage

Work in progress. Not ready for production yet!

## Create checkout session

```php
$subster = new SubsterConnector('your-api-key');

$session = $subster->checkoutSessions()->create(
    CreateCheckoutSessionData::from([
        'customer' => '01jjxr79nzc9wed41d8h2kzxtz',
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

# Testing
```bash
composer test
````

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Subster](https://github.com/subster)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
