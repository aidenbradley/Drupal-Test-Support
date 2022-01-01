<?php

namespace Drupal\Tests\test_traits\Kernel\Testing;

use Drupal\Tests\test_traits\Kernel\Testing\Decorators\DecoratedListener as Listener;
use Illuminate\Support\Collection;

trait WithoutEventSubscribers
{
    /** @var array */
    private $ignoredSubscribers;

    /** @var array */
    private $ignoredEvents;

    /**
     * Prevents event subscribers from acting when an event is triggered.
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

    private function removeSubscriber(Listener $listener, ?string $event = null): self
    {
        $this->ignoredSubscribers[$listener->getServiceId()] = $listener;

        if ($event) {
            $this->ignoredEvents[$event] = $event;
        }

        $this->container->get('event_dispatcher')->removeSubscriber($listener->getOriginal());

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
        collect($eventNames)->each(function(string $event) {
            $this->getListeners($event)->each(function (Listener $listener) use ($event) {
                $this->removeSubscriber($listener, $event);
            });
        });

        return $this;
    }

    protected function enableModules(array $modules): void
    {
        parent::enableModules($modules);

        if (isset($this->ignoredSubscribers)) {
            $this->withoutSubscribers(
                collect($this->ignoredSubscribers)->keys()->toArray()
            );
        }

        if (isset($this->ignoredEvents) === false) {
            return;
        }

        $this->withoutSubscribersForEvents(
            collect($this->ignoredEvents)->keys()->toArray()
        );
    }

    private function getListeners(?string $event = null): Collection
    {
        $listeners = $this->container->get('event_dispatcher')->getListeners($event);

        return collect($listeners)->unless($event, function(Collection $listeners) {
            return $listeners->values()->collapse();
        })->mapInto(Listener::class);
    }
}
