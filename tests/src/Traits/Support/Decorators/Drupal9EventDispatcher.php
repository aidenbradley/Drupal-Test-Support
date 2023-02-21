<?php

namespace Drupal\Tests\test_support\Traits\Support\Decorators;
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

    /** @phpstan-ignore-next-line */
    public function addListener($eventName, $listener, $priority = 0)
    {
        return $this->eventDispatcher->addListener($eventName, $listener, $priority);
    }

    public function addSubscriber(EventSubscriberInterface $subscriber): void
    {
        $this->eventDispatcher->addSubscriber($subscriber);
    }

    public function removeListener($eventName, $listener): void
    {
        $this->eventDispatcher->removeListener($eventName, $listener);
    }

    public function removeSubscriber(EventSubscriberInterface $subscriber): void
    {
        $this->eventDispatcher->removeSubscriber($subscriber);
    }

    public function getListeners(?string $eventName = null): array
    {
        return $this->eventDispatcher->getListeners($eventName);
    }

    /** @param null|string|mixed $event */
    public function dispatch($event, string $eventName = null): object
    {
        $this->registerDispatchedEvent($event, $eventName);

        return $this->eventDispatcher->dispatch($event, $eventName);
    }

    /**
     * @param string|mixed $eventName
     * @param callable|mixed $listener
     */
    public function getListenerPriority($eventName, $listener): ?int
    {
        return $this->eventDispatcher->getListenerPriority($eventName, $listener);
    }

    public function hasListeners(?string $eventName = null)
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
