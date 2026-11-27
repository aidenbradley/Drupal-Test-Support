<?php

/**
 * Installs this module's test-running dependencies at the surrounding
 * Drupal site's own root, not this module's.
 *
 * This module's own composer.json deliberately has no phpunit/phpunit or
 * brianium/paratest dependency (see README.md's "Running tests locally"
 * section for why) - PHPUnit has to come from the site's own
 * drupal/core-dev instead, matched to whichever exact Drupal core version
 * that site has installed. A contributor would otherwise have to work
 * out that version and the right composer invocation by hand; this
 * script is that invocation, run once after cloning this module into a
 * site.
 *
 * Not wired up as a post-install-cmd: unlike tools/phpstan (also
 * installed via a bin/ script, but isolated to its own composer.json),
 * this mutates the surrounding SITE's own composer.json/lock, a much
 * larger side effect than this module's own install should trigger
 * automatically on every `composer install`.
 */

require __DIR__ . '/../vendor/autoload.php';

$finder = new \DrupalFinder\DrupalFinder();

if ($finder->locateRoot(getcwd()) === false) {
    fwrite(STDERR, "Could not locate a Drupal site root above " . getcwd() . ". Run this from within a site this module has been cloned into.\n");
    exit(1);
}

$drupalRoot = $finder->getDrupalRoot();
$siteRoot = $finder->getComposerRoot();

$drupalPhp = file_get_contents($drupalRoot . '/core/lib/Drupal.php');

if ($drupalPhp === false || preg_match("/const VERSION = '([^']+)'/", $drupalPhp, $matches) !== 1) {
    fwrite(STDERR, "Could not determine the installed Drupal core version from {$drupalRoot}/core/lib/Drupal.php.\n");
    exit(1);
}

$coreVersion = $matches[1];

fwrite(STDERR, "Installing test dependencies at {$siteRoot} for Drupal {$coreVersion}...\n");

// Composer sets COMPOSER_BINARY to the exact binary that invoked this
// script. A bare `composer` can't be trusted instead: the require below
// pulls in drupal/core-dev, which installs its own bundled
// composer/composer as a transitive dependency, generating a
// vendor/bin/composer proxy at the site root. That proxy sits earlier in
// $PATH than any system-wide composer once a script's own vendor/bin is
// prepended to it (Composer's standard script behaviour), so a second
// run of this script - or even this same run, after that install - would
// otherwise silently invoke a different, older Composer version instead
// of the one actually running this script.
$composer = escapeshellarg(getenv('COMPOSER_BINARY') ?: 'composer');

// Older Drupal core composer.json predates allow-plugins; both site-root
// commands below rely on plugins (e.g. composer/installers) being
// permitted to run, same as this repo's own CI has to do per Drupal
// version it builds against.
passthru($composer . ' config allow-plugins true --working-dir=' . escapeshellarg($siteRoot), $exitCode);

if ($exitCode !== 0) {
    exit($exitCode);
}

passthru($composer . ' config policy.advisories.block false --working-dir=' . escapeshellarg($siteRoot), $exitCode);

if ($exitCode !== 0) {
    exit($exitCode);
}

// brianium/paratest is a CI-only speed optimization (parallelizes the
// test run), never required for local development - installed here too
// anyway, since it has no bearing on correctness and having it available
// locally lets a contributor reproduce CI's own invocation exactly.
$command = sprintf(
    '%s --no-interaction --no-progress require --dev %s phpspec/prophecy-phpunit:^2 brianium/paratest --with-all-dependencies --working-dir=%s',
    $composer,
    escapeshellarg('drupal/core-dev:' . $coreVersion),
    escapeshellarg($siteRoot)
);

passthru($command, $exitCode);
exit($exitCode);
