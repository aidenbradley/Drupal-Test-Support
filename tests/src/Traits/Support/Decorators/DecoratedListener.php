<?php

namespace AidenBradley\DrupalTestSupport\Support\Decorators;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DecoratedListener
{
    /** @var \Symfony\Component\EventDispatcher\EventSubscriberInterface|null */
    private $listener;

    /** @var string|null */
    private $serviceId;

    /** @param array{0?: \Symfony\Component\EventDispatcher\EventSubscriberInterface, 1?: string, 2?: string} $listener */
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

    /** @param array<string>|array<class-string> $listeners */
    public function inList(array $listeners): bool
    {
        return in_array($this->getClass(), $listeners) || in_array($this->getServiceId(), $listeners);
    }

    public function getOriginal(): ?EventSubscriberInterface
    {
        return $this->listener;
    }
}
