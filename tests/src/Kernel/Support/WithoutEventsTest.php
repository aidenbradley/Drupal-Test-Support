<?php

namespace Drupal\Tests\test_support\Kernel\Support;

use Drupal\KernelTests\KernelTestBase;
use Drupal\locale\LocaleEvent;
use AidenBradley\DrupalTestSupport\Support\WithoutEvents;

class WithoutEventsTest extends KernelTestBase
{
    use WithoutEvents;

    /** @test */
    public function without_events(): void
    {
        $this->withoutEvents();

        $event = $this->createEvent();

        $this->container->get('event_dispatcher')->dispatch($event, 'test_event');

        $this->assertDispatched('test_event');

        $eventClass = get_class($event);

        if ($eventClass === false) {
            $this->fail('Could not resolve class string of event');
        }

        $this->assertDispatched($eventClass);
    }

    /** @test */
    public function expects_events_class_string(): void
    {
        $event = $this->createEvent();

        $eventClass = get_class($event);

        if ($eventClass === false) {
            $this->fail('Could not resolve event class');
        }

        $this->expectsEvents($eventClass);

        $this->container->get('event_dispatcher')->dispatch($event, 'test_event');
    }

    /** @test */
    public function expects_events_event_name(): void
    {
        $this->expectsEvents('test_event');

        $this->container->get('event_dispatcher')->dispatch($this->createEvent(), 'test_event');
    }

    /** @test */
    public function doesnt_expect_events_class_string(): void
    {
        $eventClass = get_class($this->createEvent());

        if ($eventClass === false) {
            $this->fail('Could not resolve event class');
        }

        $this->doesntExpectEvents($eventClass);

        $this->container->get('event_dispatcher')->dispatch(new LocaleEvent([]), 'second_event');
    }

    /** @test */
    public function doesnt_expect_events_event_name(): void
    {
        $this->doesntExpectEvents('first_event');

        $this->container->get('event_dispatcher')->dispatch(new LocaleEvent([]), 'second_event');
    }

    /** @test */
    public function assert_dispatched_with_callback(): void
    {
        $this->expectsEvents('test_event');

        $langcodes = [
            'en',
            'de',
            'fr',
        ];

        $event = new LocaleEvent($langcodes);

        $this->container->get('event_dispatcher')->dispatch($event, 'test_event');

        $this->assertDispatched('test_event', function (LocaleEvent $firedEvent) use ($langcodes) {
            return $firedEvent->getLangcodes() === $langcodes;
        });

        /** @param  object  $firedEvent */
        $this->assertDispatched(get_class($event), function (LocaleEvent $firedEvent) use ($langcodes) {
            return $firedEvent->getLangcodes() === $langcodes;
        });
    }

    /** @return mixed */
    private function createEvent()
    {
        $eventClasses = [
            '\Symfony\Component\EventDispatcher\Event', // Drupal 9
            '\Symfony\Contracts\EventDispatcher\Event', // Drupal 10
        ];

        foreach ($eventClasses as $class) {
            if (class_exists($class) === false) {
                continue;
            }

            return new $class();
        }

        throw new \Exception(
            'None of the following event classes exist' . implode(', ', $eventClasses),
        );
    }
}
