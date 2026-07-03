# Subster PHP SDK Agent Instructions

This repository is the official PHP SDK for Subster. It is a Composer library for PHP 8.1+ built with Saloon v4, Pest, and Pint.

## Local Skills

- Use `subster-sdk-development` when adding or changing SDK endpoints, DTOs, Saloon requests, resources, response hydration, request serialization, tests, or README examples.
- Use `subster-sdk-release` when preparing versions, changelog entries, release notes, Packagist updates, git tags, or work based on `RELEASING.md`.

These repo-local `.ai` skills are for SDK maintainers. Keep `resources/boost/skills/subster-php-sdk` package-facing so Laravel applications that install the Composer package can discover that Boost skill.

## SDK Rules

- Preserve public compatibility within a major version. Do not reorder existing public DTO constructor parameters; append additive fields with safe defaults for non-breaking releases.
- Preserve raw-array compatibility where request data already accepts arrays.
- Follow the existing `DataObject + Request + Resource method + Pest tests + docs` pattern for new public API surfaces.
- Prefer SDK methods over raw HTTP inside examples and tests.
- Update `README.md` and `CHANGELOG.md` for public SDK behavior changes.
- Do not add runtime dependencies or change Composer autoload without explicit approval.

## Verification

- Run `composer validate --strict` when Composer metadata or package structure changes.
- Run `composer test` for SDK changes.
- Run `composer pint` only after PHP file edits.
- Run `git diff --check` before finishing.
