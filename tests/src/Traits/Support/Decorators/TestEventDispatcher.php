<?php

namespace Drupal\Tests\test_support\Traits\Support\Decorators;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class TestEventDispatcher
{
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
        if (method_exists($this->eventDispatcher, $name) === false) {
            return $this;
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

    /** @return mixed */
    public function __get($name)
    {
        return $this->eventDispatcher->$name;
    }
}
