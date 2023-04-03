<?php

$finder = new \DrupalFinder\DrupalFinder();
$finder->locateRoot(getcwd());

/** @var \Composer\Autoload\ClassLoader $testAutoloader */
$testAutoloader = require __DIR__ . '/vendor/autoload.php';

/** @var \Composer\Autoload\ClassLoader $drupalAutoloader */
$drupalAutoloader = require $finder->getDrupalRoot() . '/autoload.php';

$drupalAutoloader->addClassMap($testAutoloader->getClassMap());

require_once $finder->getDrupalRoot() . '/core/tests/bootstrap.php';
