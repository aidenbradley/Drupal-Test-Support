<?php

namespace Drupal\Tests\test_support\Traits\Support\Decorators;
use Illuminate\Support\Collection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DecoratedEventDispatcher implements EventDispatcherInterface
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

    public function addListener(string $eventName, callable $listener, int $priority = 0)
    {
        return $this->eventDispatcher->addListener($eventName, $listener, $priority);
    }

    public function addSubscriber(EventSubscriberInterface $subscriber)
    {
        return $this->eventDispatcher->addSubscriber($subscriber);
    }

    public function removeListener(string $eventName, callable $listener)
    {
        return $this->eventDispatcher->removeListener($eventName, $listener);
    }

    public function removeSubscriber(EventSubscriberInterface $subscriber)
    {
        return $this->eventDispatcher->removeSubscriber($subscriber);
    }

    public function getListeners(string $eventName = null): array
    {
        return $this->eventDispatcher->getListeners($eventName);
    }

    public function dispatch(object $event, string $eventName = null): object
    {
        $this->registerDispatchedEvent($event, $eventName);

        return $this->eventDispatcher->dispatch($event, $eventName);
    }

    public function getListenerPriority(string $eventName, callable $listener): ?int
    {
        return $this->eventDispatcher->getListenerPriority($eventName, $listener);
    }

    public function hasListeners(string $eventName = null): bool
    {
        return $this->eventDispatcher->hasListeners($eventName);
    }

    public function getFiredEvents(?string $event = null): Collection
    {
        return collect($this->firedEvents)->when($event, function(Collection $events, $event) {
            return $events->filter(function($object, string $name) use ($event) {
                return get_class($object) === $event || $name === $event;
            });
        });
    }

    private function registerDispatchedEvent(object $event, string $eventName = null): void
    {
        $this->firedEvents[$eventName] = $event;
    }
}
