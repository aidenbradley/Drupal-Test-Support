<?php

// DrupalFinder itself comes from this module's own vendor - requiring
// it before instantiating the class means that's always true
// regardless of what any already-active autoloader (e.g. the site
// root's own, when this bootstrap runs via the root's paratest rather
// than this module's own) happens to already know about.
/** @var \Composer\Autoload\ClassLoader $testAutoloader */
$testAutoloader = require __DIR__ . '/vendor/autoload.php';

$finder = new \DrupalFinder\DrupalFinder();
$finder->locateRoot(getcwd());

// Composer always registers a newly-required autoloader with $prepend =
// true, so this module's own autoloader would otherwise sit ahead of the
// surrounding Drupal site's own (required below) for every class resolved
// from here on - including this module's own, separately-locked copies of
// phpunit/paratest, which can genuinely differ in version from whatever
// the site itself needs, causing class-declaration fatals. Re-register it
// appended instead: it still needs to stay active, since this module's
// own real dependencies (illuminate/collections, carbon, opis/closure)
// aren't known to the site's own autoloader at all, but only as a
// fallback for classes the site doesn't already provide. This module's
// own `Drupal\Tests\test_support\` and `Drupal\test_support_*` namespaces
// need no such handling here: core/tests/bootstrap.php (below) already
// autoloads every discovered extension's own tests/src and src
// directories, including this one's, since it's physically present
// under the site's modules directory regardless of Composer dependency
// graphs.
$testAutoloader->unregister();
$testAutoloader->register(false);

require $finder->getDrupalRoot() . '/autoload.php';
require_once $finder->getDrupalRoot() . '/core/tests/bootstrap.php';
