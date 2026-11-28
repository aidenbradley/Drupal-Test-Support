<?php declare(strict_types=1);

// mglaman/phpstan-drupal 2.x discovers the Drupal root via
// webflo/drupal-finder's DrupalFinderComposerRuntime, which relies
// entirely on Composer\InstalledVersions - populated only from this
// isolated tool's own installed.php, which has no idea drupal/core
// exists at all, since this tool deliberately never depends on it
// directly (see this directory's own composer.json description for
// why). Composer\InstalledVersions merges in any other loaded
// Composer runtime's own installed.php automatically, the same way
// it would for any nested/plugin install - it just needs that
// runtime to have actually been required first. Locating and
// requiring the surrounding site's own vendor/autoload.php here, via
// the older, filesystem-walking DrupalFinder (not the composer-
// runtime-dependent one - this is exactly the chicken-and-egg problem
// it doesn't have), does that early enough for
// DrupalFinderComposerRuntime to then find drupal/core successfully
// when phpstan-drupal's own bootstrap runs next.
$finder = new \DrupalFinder\DrupalFinder();

if ($finder->locateRoot(getcwd()) === false) {
    throw new \RuntimeException('Could not locate a Drupal site root above ' . getcwd() . '.');
}

require $finder->getVendorDir() . '/autoload.php';
