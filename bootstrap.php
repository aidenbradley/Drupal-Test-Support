<?php

$finder = new \DrupalFinder\DrupalFinder();
$finder->locateRoot(getcwd());

require_once $finder->getDrupalRoot() . '/core/tests/bootstrap.php';
