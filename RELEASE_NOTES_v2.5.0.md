# Subster PHP SDK v2.5.0

Subster PHP SDK v2.5.0 adds safe two-step subscription plan changes.

Use `subscriptions()->previewPlanChange()` to create a 15-minute quote and display its exact amount due, credit, recurring target price, effective time, and expiry. Confirm only after explicit customer approval by passing the quote ID and an idempotency key to `changePlan()`.

The release is additive and compatible with existing v2 integrations. Existing `ChangeSubscriptionPlanData` positional and named constructors continue to work, and unquoted plan-change calls are unchanged.

When the API reports `quote_expired` or `quote_stale`, request and display a fresh quote before asking the customer to confirm again. Do not automatically retry payment. Reuse the original quote ID and idempotency key only when recovering an ambiguous confirmation attempt with the same request fields.
