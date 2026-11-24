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
