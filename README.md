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
`composer.lock`. This is deliberate, not an accident of refactoring:

- `mglaman/phpstan-drupal`'s 2.x line (needed to analyse newer core versions correctly —
  its 1.x line crashes on core's newer services.yml deprecation format) requires PHP
  8.1+ itself, directly conflicting with this module's own PHP >=7.3 floor. Keeping it
  in a separate `composer.json` means its own PHP requirement never affects what this
  module's actual runtime/test dependencies resolve to.
- Separately, and more fundamentally: `phpstan-drupal` 2.x determines the Drupal root by
  asking Composer's own `InstalledVersions` registry whether `drupal/core` is installed
  *in the same vendor that invoked it* — not by walking the filesystem like its 1.x line
  did. Since this module always gets its own independent `vendor/` wherever it's cloned
  (never merged into the surrounding site's own `composer.json`), that check will always
  fail if phpstan/phpstan-drupal are installed as part of this module's own dependency
  graph, regardless of PHP version. There is no version of phpstan-drupal's 2.x line that
  works around this; it's how that whole major version is designed to work.

**How it's still invisible day-to-day:**

- `composer install`/`composer update` in this module's own directory automatically
  installs `tools/phpstan`'s vendor too, via a `post-install-cmd`/`post-update-cmd` hook
  (`bin/install-static-analysis-tooling.php`) — one command, nothing extra to remember.
  That hook silently does nothing when run under PHP <8.1 (e.g. inside an older PHP
  container installing this module's actual Kernel/Unit test dependencies), rather than
  failing the whole install.
- `composer test:types` still works exactly as before, from wherever you'd normally run
  it (inside `modules/custom/test_support` of whichever Drupal site you're testing
  against). It bridges `phpstan-drupal`'s Composer-runtime detection to that surrounding
  site's own autoloader via `--autoload-file=../../../../vendor/autoload.php`, letting it
  correctly see `drupal/core` while phpstan/phpstan-drupal themselves stay installed in
  this module's own isolated `tools/phpstan`, not the site's.
- That relative path assumes the standard `web/modules/custom/{name}` nesting depth
  (what `composer/installers` uses by default for `type: drupal-custom-module`). This
  only ever matters in environments this module's own maintainers control directly — the
  local compatibility-testing sites and CI's matrix jobs — never for a downstream
  developer who simply requires this module into their own project, since they'd never
  run this module's own internal test/lint/static-analysis scripts at all.
