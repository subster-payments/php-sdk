---
name: subster-sdk-release
description: Prepare Subster PHP SDK releases. Use when updating CHANGELOG, README, RELEASING, SemVer decisions, release notes, Packagist publishing, git tags, Composer package readiness, or post-release verification for subster/php-sdk.
---

# Subster SDK Release

Use this skill when preparing or explaining a `subster/php-sdk` release. Keep release work traceable, SemVer-compatible, and Packagist-friendly.

## Start Here

- Read `RELEASING.md` first if it exists.
- Inspect the current diff and status before advising version numbers or tag commands.
- Do not stage, unstage, amend, move tags, or push unless the user explicitly asks.
- Keep `composer.json` versionless. Packagist versions should come from git tags.
- Do not change runtime SDK API, DTOs, requests/resources, autoload, or dependencies as part of release notes work unless the user asks for implementation.

## Version Choice

- Use patch releases for docs, AI skills, tests, internal hardening, and compatible bug fixes.
- Use minor releases for additive SDK features, new endpoints, new optional DTO fields, and new helper methods.
- Use major releases only for breaking public constructor, method, namespace, dependency, PHP-version, or behavior changes.
- Do not go back to `0.x` versioning after a stable `1.x` line is published.

## Release Checklist

- Confirm `CHANGELOG.md` has a clear entry for the change.
- Confirm `README.md` reflects new public behavior or installation guidance when relevant.
- Run `composer validate --strict`.
- Run `composer test`.
- Run `composer pint` only if PHP files changed.
- Run `git diff --check`.
- Confirm no unrelated files are included in the release commit.

## Tagging And Packagist

After the intended commit is pushed to `main` and checks are acceptable, the normal release flow is:

```bash
git tag -a vX.Y.Z -m "vX.Y.Z"
git push origin vX.Y.Z
```

Packagist should detect the new tag for `subster/php-sdk`. If automatic updates lag, refresh the package from Packagist after verifying the tag exists on GitHub.

## Release Notes

- Keep release notes short and consumer-facing.
- Mention new SDK methods and public behavior in terms developers will search for, such as `subscriptions()->recordUsage()` or `invoices()->all()`.
- For AI/docs-only releases, describe them as maintenance or developer-experience improvements.
- Include links to `README.md`, `CHANGELOG.md`, and `https://subster.ru/docs/api#/` when useful.
