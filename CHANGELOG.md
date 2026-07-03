# Changelog

All notable changes to `subster-payments/php-sdk` will be documented in this file.

## v2.0.1 - 2026-07-03

- Replaced public response date DTO types from `Carbon\CarbonImmutable` with native `DateTimeImmutable`.
- Removed the direct `nesbot/carbon` runtime dependency from the SDK.
- Kept request date inputs compatible with `DateTimeInterface|string|null`; date-only strings are still preserved as strings.

## v2.0.0 - 2026-07-03

- Renamed the Composer package from `subster/php-sdk` to `subster-payments/php-sdk`.
- Added checkout promotion code support and typed invoice discount data.
- Added strict backed enum DTO fields for finite API values such as invoice status, checkout status, webhook event, plan type, pricing model, coupon discount type and duration, subscription plan change mode and proration behavior, trial interval, subscription status, and currency.
- Breaking change: finite DTO string fields now hydrate as `Subster\PhpSdk\Enums\*` cases. Use enum cases for direct DTO construction and compare response fields with enum cases instead of raw strings.
- Breaking change: response dates now hydrate as `Carbon\CarbonImmutable`.
- Breaking change: `SubscriptionPlanChangeData` now exposes the plan change `id` plus nullable `checkout_session` and `checkout_url` fields.
- Breaking change: invoice discount coupons now expose `api_identifier` instead of `api_id`.
- Changed request date inputs such as invoice paid-at filters and subscription usage `occurred_at` to accept `DateTimeInterface|string|null`.
- Changed `CustomerData::$name` to nullable and omitted null customer names from create requests.

## v1.0.2 - 2026-07-03

- Removed the accidental `livewire/blaze` runtime dependency.

## v1.0.1 - 2026-07-03

- Added repo-local AI skills for SDK development and release workflows.
- Added a Laravel Boost skill for Subster PHP SDK integration guidance.

## v1.0.0 - 2026-07-03

- Removed a stray debug call from customer updates.
- Added paid invoice listing with embedded customer, subscription, and invoice item data.
- Added optional checkout item quantity support via raw item arrays and `CreateCheckoutSessionItemData`.
- Added usage-based subscription usage event recording via `subscriptions()->recordUsage()`.
- Added optional `proration_behavior` support to subscription plan changes.
- Added nullable `pricing_model` to invoice item data.
- Documented initial checkout quantity for usage-based recurring prices.
