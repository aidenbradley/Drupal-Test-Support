<?php

namespace Drupal\Tests\test_support\Traits\Support\Decorators;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DecoratedListener
{
    /** @var \Symfony\Component\EventDispatcher\EventSubscriberInterface|null */
    private $listener;

    /** @var string|null */
    private $serviceId;

    public static function createFromArray(array $listener): self
    {
        return new self($listener);
    }

    public function __construct(array $listener)
    {
        $this->listener = $listener[0] ?? null;
        $this->serviceId = $listener[2] ?? null;
    }

    public function getServiceId(): ?string
    {
        return $this->serviceId;
    }

    public function getClass(): ?string
    {
        if (isset($this->listener) === false) {
            return null;
        }

        return get_class($this->listener);
    }

    public function inList(array $listeners): bool
    {
        return in_array($this->getClass(), $listeners) || in_array($this->getServiceId(), $listeners);
    }

    public function getOriginal(): ?EventSubscriberInterface
    {
        return $this->listener;
    }
}
