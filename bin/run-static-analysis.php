<?php

/**
 * Runs the isolated tools/phpstan analysis against the surrounding Drupal
 * site's own autoloader, located dynamically via webflo/drupal-finder (the
 * same mechanism bootstrap.php already uses) rather than a hardcoded
 * relative path. The module can be cloned at any depth relative to a
 * Drupal site's root - web/modules/custom/{name} locally, modules/{name}
 * in CI's drupal/drupal checkouts, or otherwise - and this still finds
 * the right vendor/autoload.php to bridge phpstan-drupal's Composer-
 * runtime Drupal-root detection to.
 */

require __DIR__ . '/../vendor/autoload.php';

$finder = new \DrupalFinder\DrupalFinder();
$finder->locateRoot(getcwd());

$drupalVendorDir = $finder->getVendorDir();

if ($drupalVendorDir === null) {
    fwrite(STDERR, "Could not locate the surrounding Drupal site's vendor directory from " . getcwd() . "\n");
    exit(1);
}

$command = sprintf(
    'php -d memory_limit=4G %s analyse -c %s --autoload-file=%s',
    escapeshellarg(__DIR__ . '/../tools/phpstan/vendor/bin/phpstan'),
    escapeshellarg(__DIR__ . '/../phpstan.neon'),
    escapeshellarg($drupalVendorDir . '/autoload.php')
);

passthru($command, $exitCode);
exit($exitCode);
