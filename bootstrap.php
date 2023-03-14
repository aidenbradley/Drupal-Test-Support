<?php

use DrupalFinder\DrupalFinder;

$finder = new DrupalFinder();
$finder->locateRoot(getcwd());
$root = $finder->getDrupalRoot();
$vendor = $finder->getVendorDir();

require 'vendor/autoload.php';

require $vendor . '/autoload.php';

require $root . '/core/tests/bootstrap.php';
