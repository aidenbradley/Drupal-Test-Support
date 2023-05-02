<?php

namespace AidenBradley\DrupalTestSupport\Support;

use AidenBradley\DrupalTestSupport\Support\Contracts\TestEventDispatcher;
use AidenBradley\DrupalTestSupport\Support\Decorators\EventDispatcher\DecoratedEventDispatcher;

trait WithoutEvents
{
    /** @var string[]|class-string[] */
    private $expectedEvents = [];

    /** @var string[]|class-string[] */
    private $nonExpectedEvents = [];

    /** Mock the event dispatcher. All dispatched events are collected */
    public function withoutEvents(): self
    {
        $this->container->set('event_dispatcher', DecoratedEventDispatcher::create(
            $this->container->get('event_dispatcher')
        ));

        return $this;
    }

    /** @param string|string[]|class-string[] $events */
    public function expectsEvents($events): self
    {
        $this->expectedEvents = (array) $events;

        return $this->withoutEvents();
    }

    /** @param string|string[]|class-string[] $events */
    public function doesntExpectEvents($events): self
    {
        $this->nonExpectedEvents = (array) $events;

        return $this->withoutEvents();
    }

    /** @param class-string|string|null $event */
    public function assertDispatched($event, ?callable $callback = null): self
    {
        $firedEvents = $this->eventDispatcher()->getFiredEvents($event);

        $this->assertTrue($firedEvents->isNotEmpty(), $event . ' event was not dispatched');

        if ($callback) {
            $this->assertTrue($callback($firedEvents->first()));
        }

        return $this;
    }

    public function assertNotDispatched(?string $event): self
    {
        $this->assertTrue($this->eventDispatcher()->getFiredEvents($event)->isEmpty(), $event . ' event was dispatched');

        return $this;
    }

    protected function tearDown(): void
    {
        if ($this->expectedEvents !== []) {
            foreach ($this->expectedEvents as $event) {
                $this->assertDispatched($event);
            }
        }

        if ($this->nonExpectedEvents !== []) {
            foreach ($this->nonExpectedEvents as $event) {
                $this->assertNotDispatched($event);
            }
        }

        parent::teardown();
    }

    private function eventDispatcher(): TestEventDispatcher
    {
        /** @phpstan-ignore-next-line */
        return $this->container->get('event_dispatcher');
    }
}
