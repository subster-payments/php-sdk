---
name: subster-php-sdk
description: Integrate the official Subster PHP SDK in Laravel applications. Use when building or changing Subster billing, payments, hosted checkout, billing portal, subscription plan changes, usage-based billing snapshots, paid invoice sync, or customer synchronization with subster/php-sdk.
---

# Subster PHP SDK

## Core Rules

- Prefer `subster/php-sdk` over raw HTTP when the SDK exposes the needed endpoint.
- Keep Subster API keys in environment-backed Laravel config. Never hard-code tokens in source code, tests, prompts, or docs.
- Use `Subster\PhpSdk\SubsterConnector` as the SDK entrypoint.
- Check the installed SDK version and public DTOs before assuming fields or methods exist.
- Preserve existing SDK compatibility: do not remove raw-array support where it already exists, and do not change public DTO constructor order in a breaking way.
- Do not invent a webhook helper. This SDK currently covers API client workflows; consult the official API docs for webhook payload contracts.
- When the API contract is unclear, check https://subster.ru/docs/api#/ before changing application behavior.

## Installation Check

Confirm the application requires the package:

```bash
composer require subster/php-sdk
```

Use an environment variable for the API token:

```dotenv
SUBSTER_API_KEY=
```

Read it through Laravel config, for example `config/services.php`:

```php
'subster' => [
    'api_key' => env('SUBSTER_API_KEY'),
],
```

Create the connector from config:

```php
use Subster\PhpSdk\SubsterConnector;

$subster = new SubsterConnector(config('services.subster.api_key'));
```

If several classes need the connector, wrap connector creation in a small application service or container binding instead of repeating env/config reads across controllers, jobs, and actions.

## Customer And Checkout

Use the SDK to create or reuse a Subster customer, then create a hosted checkout session.

```php
use Subster\PhpSdk\DataObjects\CreateCheckoutSessionData;
use Subster\PhpSdk\DataObjects\CreateCustomerData;

$customer = $subster->customers()->create(
    CreateCustomerData::from([
        'email' => $user->email,
        'name' => $user->name,
    ])
);

$session = $subster->checkoutSessions()->create(
    CreateCheckoutSessionData::from([
        'customer' => $customer->id,
        'items' => [
            [
                'plan' => $substerPlanId,
            ],
        ],
        'success_url' => route('billing.success'),
        'cancel_url' => route('billing.cancel'),
    ])
);

return redirect()->away($session->url);
```

Use `checkoutSessions()->get($sessionId)` when the application needs to poll or verify checkout status server-side.

## Checkout Items

Checkout `items` may be raw arrays or `CreateCheckoutSessionItemData` objects. Keep both styles working in application code unless there is a local reason to standardize.

```php
use Subster\PhpSdk\DataObjects\CreateCheckoutSessionItemData;

'items' => [
    CreateCheckoutSessionItemData::from([
        'plan' => $oneTimePlanId,
        'quantity' => 5,
    ]),
],
```

Use `quantity` for one-time package purchases or the initial invoice quantity for a usage-based recurring price. If `quantity` is omitted, Subster treats the item as quantity `1`.

## Billing Portal

Use billing portal sessions when customers should manage their subscription, payment method, or invoices in Subster-hosted UI.

```php
use Subster\PhpSdk\DataObjects\CreateBillingPortalSessionData;

$portalSession = $subster->billingPortalSessions()->create(
    CreateBillingPortalSessionData::from([
        'customer' => $substerCustomerId,
        'return_url' => route('billing.index'),
    ])
);

return redirect()->away($portalSession->url);
```

## Subscription Plan Changes

Use `subscriptions()->changePlan()` for subscription upgrades, downgrades, and package changes.

```php
use Subster\PhpSdk\DataObjects\ChangeSubscriptionPlanData;

$change = $subster->subscriptions()->changePlan(
    $substerSubscriptionId,
    ChangeSubscriptionPlanData::from([
        'plan' => $targetPlanId,
        'success_url' => route('billing.success'),
        'cancel_url' => route('billing.cancel'),
    ])
);

if ($change->mode === 'checkout' && $change->url !== null) {
    return redirect()->away($change->url);
}
```

Subster prorates immediate upgrades by default. For prepaid package plans where the customer should pay the full target plan amount, pass `proration_behavior` as `none`.

## Usage-Based Billing

For usage-based recurring subscriptions, pass the initial `quantity` during checkout and record later absolute usage snapshots with `recordUsage()`. The value is the current snapshot, not a delta.

```php
use Subster\PhpSdk\DataObjects\RecordSubscriptionUsageEventData;

$event = $subster->subscriptions()->recordUsage(
    $substerSubscriptionId,
    RecordSubscriptionUsageEventData::from([
        'quantity' => $currentSeatCount,
        'occurred_at' => now()->toIso8601String(),
        'idempotency_key' => 'subscription-'.$substerSubscriptionId.'-'.now()->toDateString(),
        'metadata' => ['source' => 'laravel-app'],
    ])
);
```

Prefer stable idempotency keys for retryable jobs so repeated attempts do not create duplicate usage records.

## Paid Invoices

Use `invoices()->all()` to sync paid invoices. Filter by customer, subscription, and paid date range when possible.

```php
use Subster\PhpSdk\DataObjects\ListInvoicesData;

$invoices = $subster->invoices()->all(ListInvoicesData::from([
    'customer' => $substerCustomerId,
    'paid_at_gte' => now()->startOfMonth()->toDateString(),
    'paid_at_lte' => now()->endOfMonth()->toDateString(),
    'limit' => 100,
]));

foreach ($invoices->data as $invoice) {
    // $invoice->customer, $invoice->subscription, and $invoice->items are included.
}
```

If `$invoices->has_more` is true, request the next page with the last invoice id as `starting_after`.

## Laravel Implementation Guidance

- Put write workflows in focused actions, jobs, or services that can be tested without calling the real Subster API.
- Store Subster IDs on local models explicitly, for example customer, subscription, checkout session, or invoice identifiers.
- Use named routes for `success_url`, `cancel_url`, and `return_url`.
- Treat payment state changes as external-system boundaries: validate local ownership before redirecting or applying updates.
- For tests, fake the application service boundary. If the app already uses Saloon Laravel integration, Saloon fakes may also be appropriate.

## Release-Safe SDK Changes

When changing the SDK itself:

- Follow the existing pattern: DataObject, Request, Resource method, typed response DTO, and Pest coverage.
- Keep request payload tests exact, especially for nullable fields that should be omitted.
- Append new public DTO constructor parameters with safe defaults instead of inserting them before existing parameters.
- Update `README.md` and `CHANGELOG.md` for new public SDK behavior.
- Run `composer pint` after PHP edits and `composer test` before finishing.
