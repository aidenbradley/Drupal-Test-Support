<?php

use Composer\Autoload\ClassLoader;
//use DrupalFinder\DrupalFinder;

//final class TestAutoloader
//{
//  /** @var ClassLoader $autoloader */
//  private static $autoloader;
//
//  final public static function loadClass(string $class)
//  {
//    return self::getAutoloader()->loadClass($class);
//  }
//
//  final public static function findFile(string $class): string
//  {
//    return static::$autoloader->findFile($class);
//  }
//
//  final public static function getAutoloader(): ClassLoader
//  {
//    if (self::$autoloader === null) {
//      self::$autoloader = require __DIR__ . '/vendor/autoload.php';
//    }
//
//    return self::$autoloader;
//  }
//}

//spl_autoload_register([TestAutoloader::class, 'loadClass'], true, true);
///** @var \Composer\Autoload\ClassLoader $testAutoloader */
//$testAutoloader = require __DIR__ . '/vendor/autoload.php';

//spl_autoload_register([$autoloader, 'loadClass'], true, true);

$finder = new \DrupalFinder\DrupalFinder();
$finder->locateRoot(getcwd());

/** @var \Composer\Autoload\ClassLoader $drupalClassLoader */
$drupalClassLoader = require $finder->getDrupalRoot() . '/autoload.php';

/** @var \Composer\Autoload\ClassLoader $testAutoloader */
$testAutoloader = require __DIR__ . '/vendor/autoload.php';

$drupalClassLoader->addClassMap($testAutoloader->getClassMap());

require_once $finder->getDrupalRoot() . '/core/tests/bootstrap.php';
