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

// Bridging phpstan-drupal's Composer-runtime detection to the site's own
// autoloader (above) lets it see drupal/core there - but if the site also
// has phpstan/extension-installer active (e.g. via drupal/core-dev), its
// autoloader will generate its OWN PHPStan\ExtensionInstaller\GeneratedConfig
// once referenced, which confuses phpstan (running from tools/phpstan/vendor,
// a completely separate installation) into thinking its extensions were
// installed by an incompatible version. We don't rely on extension-installer's
// auto-discovery at all - phpstan.neon manually includes mglaman/phpstan-
// drupal's extension.neon - so pre-empt it with an empty stub, defined before
// the site's autoloader has a chance to, via a small generated bridge file.
$bridgeFile = tempnam(sys_get_temp_dir(), 'phpstan-autoload-bridge-') . '.php';

file_put_contents($bridgeFile, sprintf(
    '<?php namespace PHPStan\ExtensionInstaller { if (!class_exists(GeneratedConfig::class, false)) { class GeneratedConfig { public const EXTENSIONS = []; } } } namespace { require %s; }',
    var_export($drupalVendorDir . '/autoload.php', true)
));

$command = sprintf(
    'php -d memory_limit=4G %s analyse -c %s --autoload-file=%s',
    escapeshellarg(__DIR__ . '/../tools/phpstan/vendor/bin/phpstan'),
    escapeshellarg(__DIR__ . '/../phpstan.neon'),
    escapeshellarg($bridgeFile)
);

passthru($command, $exitCode);

unlink($bridgeFile);

exit($exitCode);
