<?php declare(strict_types=1);

// mglaman/phpstan-drupal's own drupal-autoloader.php (bootstrapped just
// before this file, via extension.neon) loads the surrounding Drupal
// site's own vendor/autoload.php, which Composer always registers with
// $prepend = true - putting the site's own copies of phpstan-adjacent
// packages (like mglaman/phpstan-drupal itself, bundled by drupal/core-dev
// and paired there with a different phpstan/phpstan major) ahead of this
// tool's own, isolated vendor for any class not yet loaded by this point.
// That's exactly the collision that crashes EntityStorageType's own
// declaration check against the wrong ObjectType::isSuperTypeOf() signature.
// Find this tool's own loader by its vendor path and re-register it
// prepended again, restoring its priority over whatever the site's own
// install added afterwards.
$ownVendorDir = __DIR__ . '/vendor';

foreach (\Composer\Autoload\ClassLoader::getRegisteredLoaders() as $vendorDir => $loader) {
    if ($vendorDir === $ownVendorDir) {
        $loader->unregister();
        $loader->register(true);
        break;
    }
}
