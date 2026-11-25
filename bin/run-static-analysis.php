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

// Load tools/phpstan's own vendor for DrupalFinder, not the module's own
// vendor/autoload.php. The module's vendor pulls in a real PHPUnit via
// brianium/paratest's own transitive dependency, which would then compete
// with the Drupal site's own PHPUnit (via its KernelTestBase, bridged in
// below) - the same kind of version-conflicting-class problem as the
// nikic/php-parser one worked around further down. phpstan-drupal itself
// already requires webflo/drupal-finder, so it's already present here
// without that risk.
require __DIR__ . '/../tools/phpstan/vendor/autoload.php';

$finder = new \DrupalFinder\DrupalFinder();
$finder->locateRoot(getcwd());

$drupalVendorDir = $finder->getVendorDir();

if ($drupalVendorDir === null) {
    fwrite(STDERR, "Could not locate the surrounding Drupal site's vendor directory from " . getcwd() . "\n");
    exit(1);
}

// Bridging phpstan-drupal's Composer-runtime detection to the site's own
// autoloader (above) lets it see drupal/core there - but it also exposes
// us to two classes of collision with whatever the site itself happens to
// have installed, both worked around below before the site's autoloader
// is ever required:
//
// 1. If the site also has phpstan/extension-installer active (e.g. via
//    drupal/core-dev), its autoloader will generate its OWN
//    PHPStan\ExtensionInstaller\GeneratedConfig once referenced, which
//    confuses phpstan (running from tools/phpstan/vendor, a completely
//    separate installation) into thinking its extensions were installed
//    by an incompatible version. We don't rely on extension-installer's
//    auto-discovery at all - phpstan.neon manually includes
//    mglaman/phpstan-drupal's extension.neon - so pre-empt it with an
//    empty stub.
// 2. phpstan.phar bundles its own nikic/php-parser, deliberately
//    unprefixed so extensions like phpstan-drupal can reference
//    PhpParser\Node etc. directly - but it's loaded lazily, and if the
//    site also has its own (possibly older/incompatible) nikic/php-parser
//    installed with --optimize-autoloader (true for CI's drupal/drupal
//    checkouts), its classmap can resolve a class faster than phpstan's
//    own lazy PSR-4 autoloading does, letting the site's version win the
//    race for whichever specific class gets referenced later - causing a
//    'Declaration ... must be compatible with ...' fatal when the two
//    versions' method signatures differ. Force phpstan's own bundled
//    classes to resolve first by referencing them before the site's
//    autoloader is registered at all.
$bridgeFile = tempnam(sys_get_temp_dir(), 'phpstan-autoload-bridge-') . '.php';

file_put_contents($bridgeFile, sprintf(
    '<?php namespace PHPStan\ExtensionInstaller { if (!class_exists(GeneratedConfig::class, false)) { class GeneratedConfig { public const EXTENSIONS = []; } } } namespace { foreach ([\'PhpParser\Comment\', \'PhpParser\Node\', \'PhpParser\NodeFinder\', \'PhpParser\NodeTraverser\', \'PhpParser\NodeVisitor\', \'PhpParser\NodeVisitorAbstract\'] as $phpParserClass) { class_exists($phpParserClass); } require %s; }',
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
