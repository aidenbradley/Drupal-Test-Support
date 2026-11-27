<?php

namespace Drupal\Tests\test_support\Traits\Support\Decorators;

class DecoratedListener
{
    // Drupal 11.1+'s "OOP hooks" register plain objects (e.g.
    // Drupal\sqlite\Hook\SqliteHooks) as listeners too, not just
    // EventSubscriberInterface implementations - getOriginal() can't
    // narrow to that interface specifically, since a raw listener's
    // [0] element is any object tagged as a callable target. The one
    // caller (WithoutEventSubscribers::removeSubscriber()) already
    // guards with its own instanceof check before relying on
    // EventSubscriberInterface-specific behaviour.
    /** @var object|null */
    private $listener;

    /** @var string|null */
    private $method;

    /** @var string|null */
    private $serviceId;

    /** @var string|null */
    private $eventName;

    /** @param array{0?: object, 1?: string, 2?: string, 3?: string} $listener */
    public function __construct(array $listener)
    {
        $this->listener = $listener[0] ?? null;
        $this->method = $listener[1] ?? null;
        $this->serviceId = $listener[2] ?? null;
        $this->eventName = $listener[3] ?? null;
    }

    public function getServiceId(): ?string
    {
        return $this->serviceId;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function getEventName(): ?string
    {
        return $this->eventName;
    }

    public function getClass(): ?string
    {
        if (isset($this->listener) === false) {
            return null;
        }

        return get_class($this->listener);
    }

    /** @param array<string>|array<class-string> $listeners */
    public function inList(array $listeners): bool
    {
        return in_array($this->getClass(), $listeners) || in_array($this->getServiceId(), $listeners);
    }

    public function getOriginal(): ?object
    {
        return $this->listener;
    }
}
