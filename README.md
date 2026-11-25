# Test Support
Traits to be used in Drupal tests that help with test setup and assertions

## Ignored security advisories

`composer.json` (`config.policy.advisories.ignore-id`) explicitly ignores the following
Composer security advisory IDs. JSON has no comment syntax and `ignore-id` only accepts
a flat list of IDs with no room for a reason, so the rationale for each lives here instead:

- **`PKSA-z3gr-8qht-p93v`, `PKSA-qccq-2pht-gg3w`, `PKSA-5jz8-6tcw-pbk4`** — CVE-2026-24765
  and related advisories: unsafe deserialization in PHPUnit's legacy `.phpt`-format
  code-coverage handling. This module's test suite never uses the `.phpt` format or
  collects coverage through it, so the vulnerable code path is never reachable here.
  Without ignoring these, Composer refuses to resolve any PHPUnit version new enough to
  satisfy newer Drupal core versions' test-compatibility requirements. If you add a new
  ID here, add a matching bullet explaining why it's safe to ignore in this module's
  specific usage.

## Static analysis tooling lives in its own isolated `tools/phpstan`

`mglaman/phpstan-drupal` (the Drupal-aware PHPStan rules this module's static analysis
relies on) is **not** a dependency of this module's own `composer.json` — it lives in a
separate `tools/phpstan/composer.json`, with its own independent `vendor/` and
`composer.lock`. This is deliberate, not an accident of refactoring: it requires PHP
8.1+ itself, directly conflicting with this module's own PHP >=7.3 floor. Keeping it in
a separate `composer.json` means its own PHP requirement never affects what this
module's actual runtime/test dependencies resolve to.

It's pinned to `~1.2.12` specifically, not the latest `2.x` release. 2.x determines the
Drupal root by asking Composer's own `InstalledVersions` registry whether `drupal/core`
is installed *in the same vendor that invoked it* — not by walking the filesystem like
1.x does. Since this module always gets its own independent `vendor/` wherever it's
cloned (never merged into the surrounding site's own `composer.json`), that check always
fails for a module-isolated install like this one, regardless of PHP version — there's
no way to install 2.x here without that failing. `1.2.12` is the version to use, not just
any `1.x`: earlier 1.x releases crash on Drupal core's newer services.yml deprecation
format (fixed in [mglaman/phpstan-drupal#778](https://github.com/mglaman/phpstan-drupal/pull/778),
included starting at `1.2.12`), and `1.3.0` onward switches to the same
Composer-`InstalledVersions`-based detection 2.x uses, breaking the same way. `1.2.12` is
the one release with both the fix and the filesystem-based detection this isolated setup
needs.

**How it's still invisible day-to-day:**

- `composer install`/`composer update` in this module's own directory automatically
  installs `tools/phpstan`'s vendor too, via a `post-install-cmd`/`post-update-cmd` hook
  (`bin/install-static-analysis-tooling.php`) — one command, nothing extra to remember.
  That hook silently does nothing when run under PHP <8.1 (e.g. inside an older PHP
  container installing this module's actual Kernel/Unit test dependencies), rather than
  failing the whole install.
- `composer test:types` still works exactly as before, from wherever you'd normally run
  it (inside this module's own directory, wherever it's cloned within a Drupal site) —
  it's a plain `phpstan analyse` invocation against `tools/phpstan/vendor/bin/phpstan`,
  with no bridging or wrapper script needed. `phpstan-drupal` 1.x locates the surrounding
  site's root itself by walking up from the current directory, the same way
  `bootstrap.php` does via `webflo/drupal-finder` — so it works regardless of how deep
  this module is cloned relative to a Drupal site's root (`web/modules/custom/{name}`
  locally, `modules/{name}` in CI's own `drupal/drupal` checkouts, or otherwise).
