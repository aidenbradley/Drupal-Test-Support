<?php

namespace Drupal\Tests\test_support\Kernel\Support;

use Drupal\KernelTests\KernelTestBase;
use Drupal\locale\LocaleEvent;
use Drupal\Tests\test_support\Traits\Support\WithoutEvents;

class WithoutEventsTest extends KernelTestBase
{
    use WithoutEvents;

    /** @test */
    public function without_events(): void
    {
        $this->withoutEvents();

        $event = $this->createEvent();

        $this->container->get('event_dispatcher')->dispatch('test_event', $event);

        $this->assertDispatched('test_event');
        $this->assertDispatched(get_class($event));
    }

    /** @test */
    public function expects_events_class_string(): void
    {
        $event = $this->createEvent();

        $this->expectsEvents(get_class($event));

        $this->container->get('event_dispatcher')->dispatch('test_event', $event);
    }

    /** @test */
    public function expects_events_event_name(): void
    {
        $this->expectsEvents('test_event');

        $this->container->get('event_dispatcher')->dispatch('test_event', $this->createEvent());
    }

    /** @test */
    public function doesnt_expect_events_class_string(): void
    {
        $this->doesntExpectEvents(get_class($this->createEvent()));

        $this->container->get('event_dispatcher')->dispatch('second_event', new LocaleEvent([]));
    }

    /** @test */
    public function doesnt_expect_events_event_name(): void
    {
        $this->doesntExpectEvents('first_event');

        $this->container->get('event_dispatcher')->dispatch('second_event', new LocaleEvent([]));
    }

    /** @test */
    public function assert_dispatched_with_callback(): void
    {
        $this->expectsEvents('test_event');

        $event = $this->createEvent();
        $event->title = 'hello';

        $this->container->get('event_dispatcher')->dispatch('test_event', $event);

        /** @param object $firedEvent */
        $this->assertDispatched('test_event', function ($firedEvent) use ($event) {
            return $firedEvent->title === $event->title;
        });

        /** @param object $firedEvent */
        $this->assertDispatched(get_class($event), function ($firedEvent) use ($event) {
            return $firedEvent->title === $event->title;
        });
    }

    /** @return mixed */
    private function createEvent()
    {
        $eventClasses = [
            '\Symfony\Component\EventDispatcher\Event', // Drupal 9
            '\Symfony\Contracts\EventDispatcher\Event' // Drupal 10
        ];

        foreach ($eventClasses as $class) {
            if (class_exists($class) === false) {
                continue;
            }

            return new $class();
        }

        throw new \Exception(
            'None of the following event classes exist' . implode(', ', $eventClasses)
        );
    }
}
