<?php

namespace Drupal\Tests\test_support\Traits\Support;

use Drupal\Tests\test_support\Traits\Support\Decorators\DecoratedEventDispatcher;
use Illuminate\Support\Collection;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

trait WithoutEvents
{
    /** @var array */
    private $firedEvents;

    /** @var array */
    private $expectedEvents;

    /** @var array */
    private $nonExpectedEvents;

    /** Mock the event dispatcher. All dispatched events are collected */
    public function withoutEvents(): self
    {
        $dispatcher = DecoratedEventDispatcher::create(
            $this->container->get('event_dispatcher')
        );

        $this->container->set('event_dispatcher', $dispatcher);

        return $this;
    }

    public function expectsEvents($events): self
    {
        $this->expectedEvents = (array) $events;

        return $this->withoutEvents();
    }

    public function doesntExpectEvents($events): self
    {
        $this->nonExpectedEvents = (array) $events;

        return $this->withoutEvents();
    }

    public function assertDispatched($event, ?callable $callback = null): self
    {
        $firedEvents = $this->eventDispatcher()->getFiredEvents($event);

        $this->assertTrue($firedEvents->isNotEmpty(), $event . ' event was not dispatched');

        if ($callback) {
            $this->assertTrue($callback($firedEvents->first()));
        }

        return $this;
    }

    public function assertNotDispatched($event): self
    {
        $this->assertTrue($this->eventDispatcher()->getFiredEvents($event)->isEmpty(), $event . ' event was dispatched');

        return $this;
    }

    public function registerDispatchedEvent($arguments): void
    {
        $this->firedEvents[$arguments[1]] = $arguments[0];
    }

    protected function tearDown(): void
    {
        if (isset($this->expectedEvents)) {
            foreach ($this->expectedEvents as $event) {
                $this->assertDispatched($event);
            }
        }

        if (isset($this->nonExpectedEvents)) {
            foreach ($this->nonExpectedEvents as $event) {
                $this->assertNotDispatched($event);
            }
        }

        parent::teardown();
    }

    private function eventDispatcher(): DecoratedEventDispatcher
    {
        // might need a check on the class returned here?
        return $this->container->get('event_dispatcher');
    }
}
