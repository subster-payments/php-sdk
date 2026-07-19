# Subster PHP SDK

Официальный PHP SDK для [Subster](https://subster.ru/) — сервиса для приема платежей, автоплатежей и управления подписками. SDK помогает работать с Subster API из PHP-кода: создавать клиентов, запускать hosted checkout, открывать billing portal, менять тарифы подписок, передавать usage-based использование и получать оплаченные счета.

Полный контракт API, параметры запросов и ответы доступны в [официальной API-документации](https://subster.ru/docs/api#/).

## Быстрые ссылки

- [Сайт продукта](https://subster.ru/)
- [API-документация](https://subster.ru/docs/api#/)
- [Changelog](CHANGELOG.md)
- [License](LICENSE.md)

## Требования

- PHP 8.1 или выше.
- Composer.

## Установка

```bash
composer require subster-payments/php-sdk:^2.2
```

### Переход со старого имени пакета

Если в проекте уже установлен пакет `subster/php-sdk`, замените его на новое имя:

```bash
composer remove subster/php-sdk --no-update
composer require subster-payments/php-sdk:^2.2 -W
```

Namespace SDK остается прежним: `Subster\PhpSdk`.

### Переход с v1 на v2

В v2 конечные значения API гидрируются в backed enum-ы SDK. Например, `$invoice->status` теперь возвращает `Subster\PhpSdk\Enums\InvoiceStatus::Paid`, а не строку `'paid'`. При создании DTO через `::from()` можно передавать как enum case, так и backing value; при прямом вызове constructor передавайте enum case.

Response dates теперь возвращаются как native `DateTimeImmutable`, без runtime-зависимости SDK от Carbon. `SubscriptionPlanChangeData` использует поля `$id`, `$checkout_session`, `$checkout_url`, а discount coupon field называется `$api_identifier`.

## Laravel Boost и AI skills

Пакет поставляется с Laravel Boost skill для AI-ассистентов. Если в вашем Laravel-приложении установлен [Laravel Boost](https://laravel.com/docs/boost), обновите skills после установки SDK:

```bash
php artisan boost:install --skills
```

После этого AI-ассистент сможет использовать контекст Subster PHP SDK при задачах интеграции платежей, checkout, billing portal, подписок, usage-based billing и оплаченных счетов.

## Быстрый старт

Получите API-ключ в Subster и создайте клиент SDK. Ключ передается в API как Bearer token.

```php
use Subster\PhpSdk\SubsterConnector;

$subster = new SubsterConnector('your-api-key');
```

Минимальный сценарий интеграции обычно состоит из двух шагов: создать клиента Subster и отправить его на hosted checkout для оплаты тарифа. Email клиента можно не передавать, если в вашем приложении его еще нет; Subster запросит email для чека на платежной странице или в billing portal при добавлении карты.

```php
use Subster\PhpSdk\DataObjects\CreateCheckoutSessionData;
use Subster\PhpSdk\DataObjects\CreateCustomerData;

$customer = $subster->customers()->create(
    CreateCustomerData::from([
        'email' => 'customer@example.ru',
        'name' => 'Иван Петров',
    ])
);

$session = $subster->checkoutSessions()->create(
    CreateCheckoutSessionData::from([
        'customer' => $customer->id,
        'items' => [
            [
                'plan' => 'your-plan-id',
            ],
        ],
        'success_url' => 'https://example.ru/billing/success',
        'cancel_url' => 'https://example.ru/billing/cancel',
    ])
);

if ($session->checkout_url !== null) {
    // Redirect the customer to $session->checkout_url.
}
```

## Основные сценарии

### Клиенты

Создавайте и обновляйте клиентов аккаунта перед оформлением платежей.

```php
use Subster\PhpSdk\DataObjects\CreateCustomerData;
use Subster\PhpSdk\DataObjects\UpdateCustomerData;

$customer = $subster->customers()->create(
    CreateCustomerData::from([
        'email' => 'customer@example.ru',
        'name' => 'Иван Петров',
    ])
);

$customerWithoutEmail = $subster->customers()->create(
    CreateCustomerData::from([
        'name' => 'Клиент без email',
    ])
);

$updatedCustomer = $subster->customers()->update(
    UpdateCustomerData::from([
        'id' => $customer->id,
        'name' => 'Иван Сергеевич Петров',
    ])
);
```

Если email передан как `null` или не указан, SDK не отправит поле `email` в запросе создания клиента. В обновлении клиента `email: null` также означает “не менять email”.

### Checkout-сессии

Checkout-сессия возвращает URL платежной страницы Subster. В `items` сейчас передается тариф `plan`; для разовых оплат и usage-based тарифов можно указать `quantity`.

```php
use Subster\PhpSdk\DataObjects\CreateCheckoutSessionData;
use Subster\PhpSdk\DataObjects\CreateCheckoutSessionItemData;

$session = $subster->checkoutSessions()->create(
    CreateCheckoutSessionData::from([
        'customer' => 'customer-id',
        'items' => [
            CreateCheckoutSessionItemData::from([
                'plan' => 'your-one-time-plan-id',
                'quantity' => 5,
            ]),
        ],
        'success_url' => 'https://example.ru/billing/success',
        'cancel_url' => 'https://example.ru/billing/cancel',
    ])
);
```

Raw arrays также поддерживаются:

```php
'items' => [
    [
        'plan' => 'your-one-time-plan-id',
        'quantity' => 5,
    ],
],
```

Если на checkout нужно сразу применить промокод, передайте `promotion_code`:

```php
$session = $subster->checkoutSessions()->create(
    CreateCheckoutSessionData::from([
        'customer' => 'customer-id',
        'items' => [
            [
                'plan' => 'your-plan-id',
            ],
        ],
        'promotion_code' => 'SUMMER25',
        'success_url' => 'https://example.ru/billing/success',
    ])
);
```

Статус checkout-сессии можно получить по ее ID:

```php
$status = $subster->checkoutSessions()->get('checkout-session-id');
```

Чтобы сначала попытаться списать оплату с основной карты, а при отказе продолжить через тот же hosted checkout, передайте стратегию и устойчивый ключ операции:

```php
use Subster\PhpSdk\Enums\CheckoutPaymentAttemptState;
use Subster\PhpSdk\Enums\CheckoutPaymentState;
use Subster\PhpSdk\Enums\PaymentStrategy;

$session = $subster->checkoutSessions()->create(
    CreateCheckoutSessionData::from([
        'customer' => 'customer-id',
        'items' => [['plan' => 'your-one-time-plan-id', 'quantity' => 5]],
        'success_url' => 'https://example.ru/billing/success',
        'cancel_url' => 'https://example.ru/billing/cancel',
        'payment_strategy' => PaymentStrategy::DefaultThenCheckout,
        'idempotency_key' => 'top-up:customer-id:operation-id',
    ])
);

if ($session->payment_state === CheckoutPaymentState::Paid) {
    // Оплата и применение операции завершены синхронно.
} elseif ($session->payment_attempt_state === CheckoutPaymentAttemptState::NotAttempted && $session->checkout_url !== null) {
    // Пригодной основной карты нет — сразу откройте checkout_url.
} elseif ($session->payment_attempt_state === CheckoutPaymentAttemptState::Failed && $session->checkout_url !== null) {
    // Списание подтверждённо отклонено — предложите тот же checkout_url для другой карты.
} elseif ($session->payment_attempt_state === CheckoutPaymentAttemptState::Processing) {
    // Результат списания уточняется — опрашивайте эту же сессию и не создавайте новую.
}
```

`$session->amount` и `$session->currency` содержат фактическую сумму и валюту созданного invoice. У старого Subster новые поля, включая `$session->payment_attempt_state`, остаются `null`; SDK не угадывает причину по наличию URL.

Для hosted checkout `$session->url` остается совместимым строковым alias URL оплаты. Новый код должен использовать nullable `$session->checkout_url`: при синхронной оплате перенаправление не требуется, поэтому `checkout_url` равен `null`, а legacy `url` — пустой строке.

Повторяйте запрос только с тем же idempotency key и теми же параметрами операции.

При опросе checkout-сессии обрабатывайте `Canceled` и `Expired` как terminal-состояния без оплаты. Для обоих состояний Subster возвращает событие `WebhookEndpointEvent::CheckoutSessionClosed` и исходный статус в payload:

```php
use Subster\PhpSdk\Enums\CheckoutSessionStatus;
use Subster\PhpSdk\Enums\WebhookEndpointEvent;

$status = $subster->checkoutSessions()->get('checkout-session-id');

if (in_array($status->status, [CheckoutSessionStatus::Canceled, CheckoutSessionStatus::Expired], true)) {
    assert($status->event === WebhookEndpointEvent::CheckoutSessionClosed);
}
```

### Платный trial

Для подписок можно передать данные trial в `subscription_data`. Допустимые единицы длительности: `hour`, `day`, `week`, `month`, `year`.

```php
use Subster\PhpSdk\DataObjects\CheckoutSessionTrialData;
use Subster\PhpSdk\DataObjects\CheckoutSessionTrialDurationData;
use Subster\PhpSdk\DataObjects\CreateCheckoutSessionData;
use Subster\PhpSdk\DataObjects\CreateCheckoutSessionSubscriptionData;
use Subster\PhpSdk\Enums\CheckoutSessionTrialInterval;

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
                    'unit' => CheckoutSessionTrialInterval::Day,
                    'count' => 14,
                ]),
            ]),
        ]),
        'success_url' => 'https://example.ru/billing/success',
        'cancel_url' => 'https://example.ru/billing/cancel',
    ])
);
```

### Billing portal

Billing portal позволяет клиенту управлять подпиской, способом оплаты и счетами через hosted-страницу Subster.

```php
use Subster\PhpSdk\DataObjects\CreateBillingPortalSessionData;

$portalSession = $subster->billingPortalSessions()->create(
    CreateBillingPortalSessionData::from([
        'customer' => 'customer-id',
        'return_url' => 'https://example.ru/billing',
    ])
);

// Redirect the customer to $portalSession->url.
```

Для восстановления просроченного продления можно открыть сразу добавление новой основной карты. Subster немедленно повторит оплату и вернет пользователя по `return_url`:

```php
use Subster\PhpSdk\Enums\BillingPortalFlow;

$portalSession = $subster->billingPortalSessions()->create(
    CreateBillingPortalSessionData::from([
        'customer' => 'customer-id',
        'return_url' => 'https://example.ru/billing/recovered',
        'flow' => BillingPortalFlow::PaymentRecovery,
    ])
);
```

### Смена тарифа подписки

`changePlan` меняет тариф подписки. Если требуется доплата, Subster вернет checkout URL.

```php
use Subster\PhpSdk\DataObjects\ChangeSubscriptionPlanData;
use Subster\PhpSdk\Enums\SubscriptionPlanChangeMode;
use Subster\PhpSdk\Enums\PaymentStrategy;

$change = $subster->subscriptions()->changePlan(
    'subscription-id',
    ChangeSubscriptionPlanData::from([
        'plan' => 'target-plan-id',
        'success_url' => 'https://example.ru/billing/success',
        'cancel_url' => 'https://example.ru/billing/cancel',
        'payment_strategy' => PaymentStrategy::DefaultThenCheckout,
        'idempotency_key' => 'plan-change:subscription-id:operation-id',
    ])
);

if ($change->applied) {
    // Сохраненная карта была успешно списана, тариф уже изменен.
} elseif ($change->mode === SubscriptionPlanChangeMode::Checkout && $change->checkout_url !== null) {
    // Redirect the customer to $change->checkout_url.
}
```

По умолчанию Subster делает перерасчет при немедленном upgrade и учитывает неиспользованное время текущего периода. Для тарифов-пакетов, где клиент должен оплатить полную стоимость нового тарифа, передайте `SubscriptionPlanChangeProrationBehavior::None`.

```php
use Subster\PhpSdk\Enums\SubscriptionPlanChangeProrationBehavior;

$change = $subster->subscriptions()->changePlan(
    'subscription-id',
    ChangeSubscriptionPlanData::from([
        'plan' => 'larger-package-plan-id',
        'success_url' => 'https://example.ru/billing/success',
        'proration_behavior' => SubscriptionPlanChangeProrationBehavior::None,
    ])
);
```

### Usage-based подписки

Для usage-based подписок сначала передайте стартовое `quantity` при создании checkout-сессии. Для последующих периодов фиксируйте текущее значение использования через `recordUsage`. `quantity` — это абсолютный snapshot использования, а не дельта.

```php
use Subster\PhpSdk\DataObjects\CreateCheckoutSessionData;
use Subster\PhpSdk\DataObjects\RecordSubscriptionUsageEventData;

$session = $subster->checkoutSessions()->create(
    CreateCheckoutSessionData::from([
        'customer' => 'customer-id',
        'items' => [
            [
                'plan' => 'your-usage-based-plan-id',
                'quantity' => 20,
            ],
        ],
        'success_url' => 'https://example.ru/billing/success',
        'cancel_url' => 'https://example.ru/billing/cancel',
    ])
);

$event = $subster->subscriptions()->recordUsage(
    'subscription-id',
    RecordSubscriptionUsageEventData::from([
        'quantity' => 35,
        'occurred_at' => now(),
        'idempotency_key' => 'tenant-users-2026-01-16',
        'metadata' => ['source' => 'tenant-admin'],
    ])
);
```

### Оплаченные счета

Получайте оплаченные счета с фильтрами по клиенту, подписке и дате оплаты. Ответ включает данные клиента, подписки и позиции счета.

```php
use Subster\PhpSdk\DataObjects\ListInvoicesData;
use Subster\PhpSdk\Enums\InvoiceStatus;

$invoices = $subster->invoices()->all(ListInvoicesData::from([
    'customer' => 'customer-id',
    'paid_at_gte' => '2026-01-01',
    'paid_at_lte' => '2026-01-31',
    'limit' => 10,
]));

foreach ($invoices->data as $invoice) {
    // $invoice->customer, $invoice->subscription, and $invoice->items are included.
    // $invoice->subtotal_amount, $invoice->discount_amount, and $invoice->discount show applied discounts.

    if ($invoice->status === InvoiceStatus::Paid) {
        // Sync paid invoice state.
    }
}
```

`paid_at_gte`, `paid_at_lte` и `occurred_at` принимают `DateTimeInterface` или строку. Date-only строки вроде `2026-01-31` остаются строками, поэтому подходят для календарных фильтров.

Если `$invoices->has_more` равен `true`, запросите следующую страницу с ID последнего счета:

```php
$nextPage = $subster->invoices()->all(ListInvoicesData::from([
    'starting_after' => $invoices->data->items[array_key_last($invoices->data->items)]->id,
]));
```

Поля `$invoice->subtotal_amount`, `$invoice->discount_amount` и `$invoice->discount` показывают примененную скидку. Если скидки нет, `$invoice->discount_amount` равен `0.0`, а `$invoice->discount` равен `null`.

```php
if ($invoice->discount) {
    echo $invoice->discount->promotion_code->code;
    echo $invoice->discount->coupon->name;
    echo $invoice->discount->coupon->api_identifier;
}
```

Позиции счета содержат nullable поле `$item->pricing_model`. Для usage-based счетов metadata может включать детали backend meter и snapshot использования, по которому был сформирован счет.

## Ошибки и полный API-контракт

SDK использует Saloon и выбрасывает исключения для неуспешных HTTP-ответов. Для обработки ошибок ориентируйтесь на статус API-ответа и тело ошибки Subster.

Полный список endpoint-ов, обязательные поля, форматы дат, варианты валидационных ошибок и webhook-сценарии смотрите в [официальной API-документации](https://subster.ru/docs/api#/).

## Тесты

```bash
composer test
```

## Changelog

Список изменений находится в [CHANGELOG](CHANGELOG.md).

## License

The MIT License (MIT). See [License File](LICENSE.md) for more information.
