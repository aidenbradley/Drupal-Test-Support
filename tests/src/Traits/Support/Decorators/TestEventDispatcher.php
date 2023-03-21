<?php

namespace Drupal\Tests\test_support\Traits\Support\Decorators;

use Illuminate\Support\Collection;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class TestEventDispatcher
{
    /** @var array */
    private $firedEvents = [];

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public static function create(EventDispatcherInterface $eventDispatcher): self
    {
        return new self($eventDispatcher);
    }

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }


    /**
     * @param mixed $arguments
     * @return mixed
     */
    public function __call(string $name, $arguments)
    {
        if (is_countable($arguments) === false) {
            throw new \Exception('Failed to handle: ' . $name);
        }

        if ($name === 'dispatch' && count($arguments) === 2) {
            $event = collect($arguments)->filter(function ($argument): bool {
                return is_object($argument);
            })->first();

            $eventName = collect($arguments)->filter(function ($argument): bool {
                return is_string($argument);
            })->first();

            $this->registerDispatchedEvent($event, $eventName);
        }

        $return = $this->eventDispatcher->$name(...$arguments);

        if ($return instanceof EventDispatcherInterface) {
            return $this;
        }

        return $return;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function __set(string $name, $value)
    {
        if (property_exists($this->eventDispatcher, $name)) {
            $this->eventDispatcher->$name = $value;
        }

        return $this;
    }

    /**
     * @param mixed $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->eventDispatcher->$name;
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
