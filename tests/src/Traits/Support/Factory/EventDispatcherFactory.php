<?php

namespace Drupal\Tests\test_support\Traits\Support\Factory;

use Drupal\Tests\test_support\Traits\Support\Contracts\TestEventDispatcher;
use Drupal\Tests\test_support\Traits\Support\Decorators\EventDispatcher\Drupal10EventDispatcher;
use Drupal\Tests\test_support\Traits\Support\Decorators\EventDispatcher\Drupal9EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EventDispatcherFactory
{
  public static function create(EventDispatcherInterface $eventDispatcher): TestEventDispatcher
  {
    if (str_starts_with(\Drupal::VERSION, '10.')) {
      return Drupal10EventDispatcher::create($eventDispatcher);
    }

    return Drupal9EventDispatcher::create($eventDispatcher);
  }
}
