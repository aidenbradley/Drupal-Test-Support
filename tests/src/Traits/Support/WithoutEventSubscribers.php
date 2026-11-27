<?php

namespace Drupal\Tests\test_support\Traits\Support;

use Drupal\Tests\test_support\Traits\Support\Decorators\DecoratedListener;
use Drupal\Tests\test_support\Traits\Support\Decorators\DecoratedListener as Listener;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Assert;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

trait WithoutEventSubscribers
{
    /** @var Collection<string, Listener>|null */
    private $ignoredSubscribers = null;

    /** @var Collection<string, string>|null */
    private $ignoredEvents = null;

    /** @var string[]|class-string[]|null */
    private $deferredSubscribers = null;

    /**
     * Prevents event subscribers from acting when an event it's listening for is triggered.
     * Pass one or a list containing either class strings or service IDs.
     *
     * @code
     *     $this->withoutSubscribers('Drupal\node\Routing\RouteSubscriber')
     *     $this->withoutSubscribers('language.config_subscriber')
     *     $this->withoutSubscribers([
     *         'Drupal\node\Routing\RouteSubscriber',
     *         'language.config_subscriber',
     *     ]);
     *
     * @endcode
     *
     * @param string|class-string|string[]|class-string[] $listeners
     */
    public function withoutSubscribers($listeners = []): self
    {
        $this->getListeners()->when($listeners, function (Collection $collection, $listeners) {
            return $collection->filter(function (DecoratedListener $listener) use ($listeners): bool {
                return $listener->inList((array) $listeners);
            });
        })->whenEmpty(function (Collection $collection) use ($listeners) {
            $this->deferredSubscribers = array_values(array_unique(array_merge($this->deferredSubscribers ?? [], (array) $listeners)));

            return $collection;
        })->each(function (Listener $listener) {
            $this->removeSubscriber($listener);
        });

        return $this;
    }

    /**
     * Define one or a list of event names to prevent listeners
     * acting when these events are triggered
     *
     * @code
     *     $this->withoutSubscribersForEvents(\Drupal\Core\Routing\RoutingEvents::ALTER)
     *     $this->withoutSubscribersForEvents('routing.route_finished')
     *     $this->withoutSubscribersForEvents([
     *         '\Drupal\Core\Routing\RoutingEvents::ALTER',
     *         'routing.route_finished',
     *     ]);
     *
     * @endcode
     *
     * @param string|class-string|string[]|class-string[] $eventNames
     */
    public function withoutSubscribersForEvents($eventNames): self
    {
        collect((array) $eventNames)->each(function (string $event): void {
            $this->getListeners($event)->each(function (Listener $listener) use ($event): void {
                $this->removeSubscriber($listener, $event);
            });
        });

        return $this;
    }

    /**
     * @param string|class-string $listener
     * @param string|class-string|null $event
     */
    public function assertNotListening(string $listener, ?string $event = null): void
    {
        Assert::assertTrue(
            $this->getListeners($event)->filter(function (Listener $decoratedListener) use ($listener) {
                return $decoratedListener->inList((array) $listener);
            })->isEmpty()
        );
    }

    /**
     * @param string|class-string $listener
     * @param string|class-string|null $event
     */
    public function assertListening(string $listener, ?string $event = null): void
    {
        Assert::assertTrue(
            $this->getListeners($event)->filter(function (Listener $decoratedListener) use ($listener) {
                return $decoratedListener->inList((array) $listener);
            })->isNotEmpty()
        );
    }

    protected function enableModules(array $modules): void
    {
        parent::enableModules($modules);

        if ($this->ignoredSubscribers !== null) {
            $this->withoutSubscribers($this->ignoredSubscribers->keys()->all());
        }

        if ($this->deferredSubscribers !== null) {
            $this->withoutSubscribers($this->deferredSubscribers);
        }

        if ($this->ignoredEvents === null) {
            return;
        }

        $this->withoutSubscribersForEvents($this->ignoredEvents->keys()->all());
    }

    private function removeSubscriber(Listener $listener, ?string $event = null): self
    {
        /** @phpstan-ignore-next-line */
        $this->ignoredEvents = collect($this->ignoredEvents)->when($event, function (Collection $collection, string $event) {
            return $collection->put($event, $event);
        });

        $serviceId = $listener->getServiceId();

        if ($serviceId !== null) {
            $this->ignoredSubscribers = collect($this->ignoredSubscribers)->put($serviceId, $listener);
        }

        if ($listener->getOriginal() instanceof EventSubscriberInterface) {
            $this->container->get('event_dispatcher')->removeSubscriber($listener->getOriginal());
        } elseif ($listener->getOriginal() !== null && $listener->getMethod() !== null && $listener->getEventName() !== null) {
            // Drupal 11.1+'s "OOP hooks" register plain listeners directly
            // via addListener(), never addSubscriber() - removeSubscriber()
            // above was never going to remove these regardless of the
            // instanceof check, since they were never added as subscribers
            // in the first place. Remove them the same way they were added.
            $this->container->get('event_dispatcher')->removeListener(
                $listener->getEventName(),
                /** @phpstan-ignore-next-line */
                [$listener->getOriginal(), $listener->getMethod()]
            );
        }

        return $this;
    }

    /** @return Collection<int|string, Listener> */
    private function getListeners(?string $event = null): Collection
    {
        $listeners = $this->container->get('event_dispatcher')->getListeners($event);

        // The event name each listener is registered under has to be
        // captured here, before flattening loses it (getListeners(null)
        // returns listeners keyed by event name; getListeners($event)
        // returns a flat list for just that one event, so $event itself
        // is already the answer there). removeSubscriber() below needs
        // it to remove a Drupal 11.1+ "OOP hook" listener, which was
        // never added as a subscriber in the first place.
        if ($event !== null) {
            $listeners = array_map(function ($listener) use ($event) {
                return $this->withEventName($listener, $event);
            }, $listeners);
        } else {
            $withEventNames = [];

            foreach ($listeners as $eventName => $eventListeners) {
                /** @phpstan-ignore-next-line */
                foreach ($eventListeners as $listener) {
                    $withEventNames[] = $this->withEventName($listener, $eventName);
                }
            }

            $listeners = $withEventNames;
        }

        return collect($listeners)->transform(function ($listener) {
            if (is_array($listener) === false || is_object($listener[0] ?? null) === false) {
                return $listener;
            }

            $listener[2] = $this->resolveListenerServiceId($listener[0]);

            return $listener;
        })->mapInto(Listener::class);
    }

    /**
     * @param mixed $listener
     * @return mixed
     */
    private function withEventName($listener, string $eventName)
    {
        if (is_array($listener) === false) {
            return $listener;
        }

        $listener[3] = $eventName;

        return $listener;
    }

    private function resolveListenerServiceId(object $listener): ?string
    {
        if (property_exists($listener, '_serviceId')) {
            return $listener->_serviceId;
        }

        // Registered as Drupal\Component\DependencyInjection\ReverseContainer
        // (not Core) on every version that has it (9.5+/10.x/11.x) - this
        // was checking the wrong namespace, so has() always returned false
        // and this fast path was never actually reachable, on any version.
        // Harmless while getServiceIdMapping() below still worked as a
        // fallback (9.5-10.x); fatal once Drupal 11 removed it entirely,
        // since the intended fast path was never actually being taken.
        /** @phpstan-ignore-next-line */
        if ($this->container->has('Drupal\Component\DependencyInjection\ReverseContainer')) {
            /** @phpstan-ignore-next-line */
            return $this->container->get('Drupal\Component\DependencyInjection\ReverseContainer')->getId($listener);
        }

        /** @phpstan-ignore-next-line */
        $serviceMap = $this->container->get('kernel')->getServiceIdMapping();

        /** @phpstan-ignore-next-line */
        $serviceHash = $this->container->generateServiceIdHash($listener);

        return $serviceMap[$serviceHash] ?? null;
    }
}
