<?php

namespace Drupal\Tests\test_support\Traits\Support\Decorators\EventDispatcher;

use Drupal\Tests\test_support\Traits\Support\Contracts\TestEventDispatcher;
use Illuminate\Support\Collection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Drupal9EventDispatcher implements TestEventDispatcher
{
  /** @var EventDispatcherInterface */
  private $eventDispatcher;

  /** @var array */
  private $firedEvents = [];

  public static function create(EventDispatcherInterface $eventDispatcher): self
  {
    return new self($eventDispatcher);
  }

  public function __construct(EventDispatcherInterface $eventDispatcher)
  {
    $this->eventDispatcher = $eventDispatcher;
  }

  public function addListener($eventName, $listener, $priority = 0)
  {
    return $this->eventDispatcher->addListener($eventName, $listener, $priority);
  }

  public function addSubscriber(EventSubscriberInterface $subscriber)
  {
    return $this->eventDispatcher->addSubscriber($subscriber);
  }

  public function removeListener($eventName, $listener)
  {
    return $this->eventDispatcher->removeListener($eventName, $listener);
  }

  public function removeSubscriber(EventSubscriberInterface $subscriber)
  {
    return $this->eventDispatcher->removeSubscriber($subscriber);
  }

  public function getListeners($eventName = null)
  {
    return $this->eventDispatcher->getListeners($eventName);
  }

  public function dispatch($event, string $eventName = null)
  {
    $this->registerDispatchedEvent($event, $eventName);

    return $this->eventDispatcher->dispatch($event, $eventName);
  }

  public function getListenerPriority($eventName, $listener)
  {
    return $this->eventDispatcher->getListenerPriority($eventName, $listener);
  }

  public function hasListeners($eventName = null)
  {
    return $this->eventDispatcher->hasListeners($eventName);
  }

  public function getFiredEvents(?string $event = null): Collection
  {
    return collect($this->firedEvents)->when($event, function (Collection $events, $event) {
      return $events->filter(function ($object, string $name) use ($event) {
        return get_class($object) === $event || $name === $event;
      });
    });
  }

  private function registerDispatchedEvent(object $event, string $eventName = null): void
  {
    $this->firedEvents[$eventName] = $event;
  }
}
