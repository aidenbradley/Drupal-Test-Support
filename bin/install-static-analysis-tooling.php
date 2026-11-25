<?php

/**
 * Installs the isolated static-analysis tooling in tools/phpstan.
 *
 * Wired up as a post-install-cmd/post-update-cmd script so a plain
 * `composer install` (or update) automatically keeps it in sync, with
 * no separate command to remember. mglaman/phpstan-drupal requires
 * PHP 8.1+, independent of this module's own PHP >=7.3 floor, so this
 * is a no-op when run under an older PHP (e.g. inside a PHP 7.3
 * container installing the module's actual runtime/test dependencies)
 * rather than failing the whole install.
 */

if (PHP_VERSION_ID < 80100) {
    fwrite(STDERR, "Skipping tools/phpstan install: requires PHP 8.1+, this is PHP " . PHP_VERSION . ".\n");
    exit(0);
}

$toolsDir = __DIR__ . '/../tools/phpstan';
passthru('composer install --working-dir=' . escapeshellarg($toolsDir), $exitCode);
exit($exitCode);
