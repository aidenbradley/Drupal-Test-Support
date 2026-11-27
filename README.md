# Test Support
Traits to be used in Drupal tests that help with test setup and assertions

## Running tests locally

This module's own `composer.json` deliberately has no `phpunit/phpunit` or
`brianium/paratest` dependency. Its tests are Kernel tests, which always require a real,
bootable Drupal site to run - there's no "standalone module" test mode - so PHPUnit
always has to come from that surrounding site anyway, via `drupal/core-dev`. Declaring
our own version here previously caused genuine problems: it couldn't track
`drupal/core-dev`'s own per-Drupal-minor PHPUnit constraint, which splits across the
PhpUnit10/PhpUnit11 compatibility shim boundary within Drupal 11's own 0-4 minor range,
so no single module-locked version could satisfy it across that whole range.

To work on this module locally:

1. Set up a real Drupal site matching whichever core version you want to test against
   (any standard `composer create-project drupal/recommended-project` site, or similar -
   this repo's own CI builds its sites from a bare `drupal/drupal` checkout instead,
   which has no `web/` subdirectory, but either layout works).
2. Clone this module into that site's modules directory (e.g.
   `web/modules/custom/test_support`), then run a normal `composer install` from
   *this module's own* directory (not the site's root) to pull in its own dependencies.
3. From this module's own directory, run:
   ```
   composer test-support:install-site-dependencies
   ```
   (or, from anywhere else, `composer --working-dir=web/modules/custom/test_support
   test-support:install-site-dependencies` - a composer script is scoped to whichever
   `composer.json` it's defined in, so running the bare command from the site's own
   root instead fails, since that composer.json doesn't define it).

   This detects the installed Drupal core version from the surrounding site and, at
   that site's own root (not this module's), installs a matching `drupal/core-dev` -
   the same per-version PHPUnit constraint Drupal core itself maintains, rather than
   one this module would otherwise have to track independently.
4. Run tests via the site's own root, pointed at this module's `phpunit.xml` and `tests/`
   directory:
   ```
   vendor/bin/phpunit -c $PWD/web/modules/custom/test_support/phpunit.xml $PWD/web/modules/custom/test_support/tests
   ```
   (adjust the `web/` prefix if your site layout doesn't use one). The `$PWD`s matter,
   not just style: Kernel tests run each test method in its own subprocess, which ends
   up with a different working directory than the one phpunit itself started in, so a
   relative `-c` path resolves against the wrong place there and fails with
   "Could not read ... phpunit.xml" for most (though curiously not all) tests.

   CI instead runs via `brianium/paratest` (a speed optimization that parallelizes the
   run, with no bearing on correctness), which it installs itself rather than this
   script installing it for you - a contributor who wants it too can
   `composer require --dev brianium/paratest --working-dir=<site root>` and swap in
   `vendor/bin/paratest --runner WrapperRunner` for `vendor/bin/phpunit` above.

Step 3 is not wired up as part of this module's own `composer install`/`update` (unlike
the `tools/phpstan` setup below): it mutates the surrounding *site's* own
`composer.json`/`composer.lock`, a much larger side effect than this module's own
install should ever trigger automatically, so it stays an explicit, one-off command a
contributor runs themselves.

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
