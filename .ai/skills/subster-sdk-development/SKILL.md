---
name: subster-sdk-development
description: Maintain and extend the Subster PHP SDK. Use when adding or changing SDK endpoints, Saloon requests/resources, DTOs, response hydration, request serialization, checkout promotion-code support, invoice discount data, Pest tests, README examples, CHANGELOG entries, or public SDK compatibility.
---

# Subster SDK Development

Use this skill when working inside `subster-payments/php-sdk` on the SDK itself. Keep runtime API changes small, aligned with the existing Saloon v4 resource/request structure, and explicit about SemVer when a major release intentionally changes DTO types.

## Start Here

- Inspect sibling files before editing: similar `src/DataObjects/*Data.php`, `src/Requests/*Request.php`, `src/Resources/*Resource.php`, and matching `tests/Feature/*Test.php`.
- Check the official API documentation at `https://subster.ru/docs/api#/` or the backend contract before inventing paths, fields, or response shapes.
- Keep `SubsterConnector` as the entry point for SDK consumers. Add resource accessors only when a new resource surface is needed.
- Do not add coupon or promotion-code CRUD resources unless the backend exposes versioned public API endpoints for them.
- Do not invent webhook helpers unless the SDK already has one. Mention webhooks in docs only as part of the broader Subster integration and link to API docs.

## Implementation Pattern

- Model a new public endpoint as `DataObject + Request + Resource method + typed response DTO + Pest tests`.
- Put request DTOs under `src/DataObjects` when the body/query has structure or is used by callers.
- Put Saloon request classes under `src/Requests`; define the exact HTTP method, endpoint, body/query serialization, and typed DTO hydration there.
- Put user-facing entry points under `src/Resources`; keep methods short and named after the SDK action, for example `recordUsage(...)`, `changePlan(...)`, or `all(...)`.
- Prefer typed DTO responses via `createDtoFromResponse()` when existing resources do the same.

## Compatibility Rules

- Treat public DTO constructors as part of the SDK contract. Do not reorder or insert parameters before existing ones inside the same major version.
- Add new DTO fields at the end of constructors with safe defaults such as `null`, `[]`, or a clearly optional value.
- Preserve raw-array compatibility anywhere existing request DTOs or request normalizers accept arrays.
- Use explicit `toArray()` methods when the wire payload must omit null optional fields or normalize nested DTO/raw-array input.
- In SDK v2, finite API values belong in backed enums under `Subster\PhpSdk\Enums`; request bodies must serialize enum cases to API backing values.
- In SDK v2, response date properties should hydrate as native `DateTimeImmutable`, request date inputs may accept `DateTimeInterface|string|null`, and public discount coupon identifiers are named `api_identifier`.
- For additive response fields, keep hydration backward-compatible when older API responses omit the fields.

## Tests And Docs

- Add or update focused Pest feature tests for request method, endpoint, body/query payload, and response DTO hydration.
- Add unit tests when changing shared serialization in `src/Concerns`.
- Include exact body/query assertions for compatibility-sensitive payloads.
- Update `README.md` examples and `CHANGELOG.md` for public SDK behavior changes.
- Run `composer test` for SDK changes.
- Run `composer pint` only when PHP files changed.
- Run `git diff --check` before finishing.

## Current SDK Surface

- `customers()`
- `checkoutSessions()` with optional `promotion_code` during create
- `billingPortalSessions()`
- `subscriptions()`
- `invoices()` with typed `InvoiceData` discount snapshot fields
