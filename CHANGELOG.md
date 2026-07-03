# Changelog

All notable changes to `subster/php-sdk` will be documented in this file.

## Unreleased

- Added a Laravel Boost skill for Subster PHP SDK integration guidance.
- Removed a stray debug call from customer updates.
- Added paid invoice listing with embedded customer, subscription, and invoice item data.
- Added optional checkout item quantity support via raw item arrays and `CreateCheckoutSessionItemData`.
- Added usage-based subscription usage event recording via `subscriptions()->recordUsage()`.
- Added optional `proration_behavior` support to subscription plan changes.
- Added nullable `pricing_model` to invoice item data.
- Documented initial checkout quantity for usage-based recurring prices.
