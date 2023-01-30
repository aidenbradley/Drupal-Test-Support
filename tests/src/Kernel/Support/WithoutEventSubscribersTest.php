<?php

namespace Drupal\Tests\test_support\Kernel\Support;

use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Core\Config\ConfigEvents;
use Drupal\KernelTests\KernelTestBase;
use Drupal\language\EventSubscriber\ConfigSubscriber;
use Drupal\node\Routing\RouteSubscriber;
use Drupal\Tests\test_support\Traits\Support\WithoutEventSubscribers;
use Symfony\Component\HttpKernel\KernelEvents;

class WithoutEventSubscribersTest extends KernelTestBase
{
    use WithoutEventSubscribers;

    /** @var ContainerAwareEventDispatcher */
    private $eventDispatcher;

    /** @test */
    public function assert_not_listening(): void
    {
        $this->assertNotListening('system.timezone_resolver', KernelEvents::REQUEST);
    }

    /** @test */
    public function assert_listening(): void
    {
        $this->assertNotListening('system.timezone_resolver', KernelEvents::REQUEST);

        $this->enableModules([
            'system',
        ]);

        $this->assertListening('system.timezone_resolver', KernelEvents::REQUEST);
    }

    /** @test */
    public function without_event_subscribers(): void
    {
        $this->assertNotEmpty($this->eventDispatcher()->getListeners());

        $this->withoutSubscribers();

        $this->assertEmpty($this->eventDispatcher()->getListeners());
    }

    /** @test */
    public function ignores_event_subscribers_after_enabling_module(): void
    {
        $this->assertNotEmpty($this->eventDispatcher()->getListeners());

        $this->withoutSubscribers();

        $this->assertEmpty($this->eventDispatcher()->getListeners());

        $this->enableModules([
            'language',
            'node',
        ]);

        $this->assertEmpty($this->eventDispatcher()->getListeners());
    }

    /** @test */
    public function without_event_subscribers_class_list(): void
    {
        $this->enableModules([
            'language',
            'node',
        ]);

        $this->withoutSubscribers([
            RouteSubscriber::class, // node.route_subscriber
            ConfigSubscriber::class, // language.config_subscriber
        ]);

        $this->assertSubscriberNotListening('node.route_subscriber');
        $this->assertSubscriberNotListening('language.config_subscriber');
    }

    /** @test */
    public function ignores_event_subscribers_by_class_after_enabling_module(): void
    {
        $this->withoutSubscribers([
            RouteSubscriber::class, // node.route_subscriber
            ConfigSubscriber::class, // language.config_subscriber
        ]);

        $this->enableModules([
            'language',
            'node',
        ]);

        $this->assertSubscriberNotListening('node.route_subscriber');
        $this->assertSubscriberNotListening('language.config_subscriber');
    }

    /** @test */
    public function without_event_subscribers_listening_for_events(): void
    {
        $this->enableModules([
            'node',
        ]);

        $this->assertNotEmpty($this->eventDispatcher()->getListeners(ConfigEvents::SAVE));

        $this->withoutSubscribersForEvents(ConfigEvents::SAVE);

        $this->assertEmpty($this->eventDispatcher()->getListeners(ConfigEvents::SAVE));
    }

    /** @test */
    public function ignores_events_by_subscribed_events_after_enabling_modules(): void
    {
        $this->withoutSubscribersForEvents(ConfigEvents::SAVE);

        $this->enableModules([
            'node',
        ]);

        $this->assertEmpty($this->eventDispatcher()->getListeners(ConfigEvents::SAVE));
    }

    /** @test */
    public function without_event_subscribers_by_class_string_or_service_id(): void
    {
        $this->enableModules([
            'language',
            'node',
        ]);

        $this->withoutSubscribers([
            RouteSubscriber::class, // node.route_subscriber
            'language.config_subscriber',
        ]);

        $this->assertSubscriberNotListening('node.route_subscriber');
        $this->assertSubscriberNotListening('language.config_subscriber');
    }

    /** @test */
    public function ignores_event_by_class_string_or_service_id_after_enabling_modules(): void
    {
        $this->withoutSubscribers([
            RouteSubscriber::class, // node.route_subscriber
            'language.config_subscriber',
        ]);

        $this->enableModules([
            'language',
            'node',
        ]);

        $this->assertSubscriberNotListening('node.route_subscriber');
        $this->assertSubscriberNotListening('language.config_subscriber');
    }

    private function eventDispatcher(): ContainerAwareEventDispatcher
    {
        if (isset($this->eventDispatcher) === false) {
            $this->eventDispatcher = $this->container->get('event_dispatcher');
        }

        return $this->eventDispatcher;
    }

    private function assertSubscriberNotListening(string $subscriber): void
    {
        $this->assertNull(
            collect($this->container->get('event_dispatcher')->getListeners())->get($subscriber),
            'The `' . $subscriber . '` event subscriber is still listening'
        );
    }
}
