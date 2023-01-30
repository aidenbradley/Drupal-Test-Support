<?php

namespace Drupal\Tests\test_support\Traits\Support;

use Drupal\Tests\test_support\Traits\Support\Decorators\DecoratedListener as Listener;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Assert;

trait WithoutEventSubscribers
{
    /** @var Collection */
    private $ignoredSubscribers;

    /** @var Collection */
    private $ignoredEvents;

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
     * @endcode
     *
     * @param string|array $listeners
     */
    public function withoutSubscribers($listeners = []): self
    {
        $this->getListeners()->when($listeners, function (Collection $collection, $listeners) {
            return $collection->filter->inList($listeners);
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
     * @endcode
     *
     * @param string|array $eventNames
     */
    public function withoutSubscribersForEvents($eventNames): self
    {
        collect($eventNames)->each(function(string $event): void {
            $this->getListeners($event)->each(function (Listener $listener) use ($event): void {
                $this->removeSubscriber($listener, $event);
            });
        });

        return $this;
    }

    public function assertNotListening(string $listener, ?string $event = null): void
    {
        Assert::assertEmpty(
            $this->getListeners($event)->filter(function(Listener $decoratedListener) use($listener) {
                return $decoratedListener->inList((array) $listener);
            })
        );
    }

    public function assertListening(string $listener, ?string $event = null): void
    {
        Assert::assertNotEmpty(
            $this->getListeners($event)->filter(function(Listener $decoratedListener) use($listener) {
                return $decoratedListener->inList((array) $listener);
            })
        );
    }

    protected function enableModules(array $modules): void
    {
        parent::enableModules($modules);

        if (isset($this->ignoredSubscribers)) {
            $this->withoutSubscribers($this->ignoredSubscribers->keys()->toArray());
        }

        if (isset($this->ignoredEvents) === false) {
            return;
        }

        $this->withoutSubscribersForEvents($this->ignoredEvents->keys()->toArray());
    }

    private function removeSubscriber(Listener $listener, ?string $event = null): self
    {
        $this->ignoredEvents = collect($this->ignoredEvents)->when($event, function(Collection $collection, string $event) {
            return $collection->put($event, $event);
        });

        $this->ignoredSubscribers = collect($this->ignoredSubscribers)->put($listener->getServiceId(), $listener);

        $this->container->get('event_dispatcher')->removeSubscriber($listener->getOriginal());

        return $this;
    }

    private function getListeners(?string $event = null): Collection
    {
        $listeners = $this->container->get('event_dispatcher')->getListeners($event);

        return collect($listeners)->unless($event, function(Collection $listeners) {
            return $listeners->values()->collapse();
        })->transform(function(array $listener) {
            $serviceMap = $this->container->get('kernel')->getServiceIdMapping();

            $serviceHash = $this->container->generateServiceIdHash($listener[0]);

            $listener[2] = $serviceMap[$serviceHash];

            return $listener;
        })->mapInto(Listener::class);
    }
}
