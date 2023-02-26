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

        if (str_starts_with(\Drupal::VERSION, '10.')) {
            $event = new \Symfony\Contracts\EventDispatcher\Event();
        } else {
            $event = new \Symfony\Component\EventDispatcher\Event();
        }

        $this->container->get('event_dispatcher')->dispatch('test_event', $event);

        $this->assertDispatched('test_event');
        $this->assertDispatched(get_class($event));
    }

    /** @test */
    public function expects_events_class_string(): void
    {
        $this->expectsEvents(Event::class);

        $this->container->get('event_dispatcher')->dispatch(new Event(), 'test_event');
    }

    /** @test */
    public function expects_events_event_name(): void
    {
        $this->expectsEvents('test_event');

        $this->container->get('event_dispatcher')->dispatch(new Event(), 'test_event');
    }

    /** @test */
    public function doesnt_expect_events_class_string(): void
    {
        $this->doesntExpectEvents(Event::class);

        $this->container->get('event_dispatcher')->dispatch(new LocaleEvent([]), 'second_event');
    }

    /** @test */
    public function doesnt_expect_events_event_name(): void
    {
        $this->doesntExpectEvents('first_event');

        $this->container->get('event_dispatcher')->dispatch(new Event(), 'second_event');
    }

    /** @test */
    public function assert_dispatched_with_callback(): void
    {
        $this->expectsEvents('test_event');

        $event = new Event();
        $event->title = 'hello';

        $this->container->get('event_dispatcher')->dispatch($event, 'test_event');

        $this->assertDispatched('test_event', function (Event $firedEvent) use ($event) {
            return $firedEvent->title === $event->title;
        });

        $this->assertDispatched(Event::class, function (Event $firedEvent) use ($event) {
            return $firedEvent->title === $event->title;
        });
    }
}
