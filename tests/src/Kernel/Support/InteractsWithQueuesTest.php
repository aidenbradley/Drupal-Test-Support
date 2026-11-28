<?php

namespace Drupal\Tests\test_support\Kernel\Support;

use Drupal\Core\Queue\DatabaseQueue;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueInterface;
use Drupal\Core\Site\Settings;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\test_support_queue\Queue\ReliableCreateNodeQueue;
use Drupal\Tests\test_support\Traits\Support\InteractsWithQueues;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

#[RunTestsInSeparateProcesses]
class InteractsWithQueuesTest extends KernelTestBase
{
    use InteractsWithQueues;

    /** @test */
    #[Test]
    public function get_queue(): void
    {
        $this->enableModules([
            'test_support_queue',
        ]);

        $this->assertInstanceOf(
            DatabaseQueue::class,
            $this->getQueue('create_node_worker')
        );
    }

    /** @test */
    #[Test]
    public function get_reliable_queue(): void
    {
        $this->enableModules([
            'test_support_queue',
        ]);

        $this->container->set('queue', $this->customQueueFactory());

        $this->assertInstanceOf(
            ReliableCreateNodeQueue::class,
            $this->getReliableQueue('create_node_worker')
        );
    }

    /** @test */
    #[Test]
    public function add_to_queue(): void
    {
        $this->addToQueue('create_node_worker', [
            'title' => 'test title',
        ]);

        $this->assertEquals(1, $this->getQueue('create_node_worker')->numberOfItems());
    }

    /** @test */
    #[Test]
    public function process_queue(): void
    {
        $this->enableModules([
            'node',
            'user',
            'test_support_queue',
        ]);
        $this->installEntitySchema('node');
        $this->installEntitySchema('user');

        $nodeStorage = $this->container->get('entity_type.manager')->getStorage('node');

        $this->assertEmpty($nodeStorage->loadMultiple());

        $this->addToQueue('create_node_worker', [
            'title' => 'test title',
        ]);

        $this->processQueue('create_node_worker');

        $this->assertNotEmpty($nodeStorage->loadMultiple());

        $nodes = $nodeStorage->loadByProperties([
            'title' => 'test title',
        ]);

        $node = reset($nodes);

        $this->assertInstanceOf(Node::class, $node);
        $this->assertEquals('test title', $node->title->value);
    }

    /** @test */
    #[Test]
    public function clear_queue(): void
    {
        $this->assertEquals(0, $this->getQueue('create_node_worker')->numberOfItems());

        $this->addToQueue('create_node_worker', [
            'title' => 'test title',
        ]);

        $this->assertEquals(1, $this->getQueue('create_node_worker')->numberOfItems());

        $this->clearQueue('create_node_worker');

        $this->assertEquals(0, $this->getQueue('create_node_worker')->numberOfItems());
    }

    /** @test */
    #[Test]
    public function queue_count(): void
    {
        $this->assertEquals(0, $this->getQueueCount('create_node_worker'));

        $this->addToQueue('create_node_worker', [
            'title' => 'test title',
        ]);

        $this->assertEquals(1, $this->getQueueCount('create_node_worker'));

        $this->clearQueue('create_node_worker');

        $this->assertEquals(0, $this->getQueueCount('create_node_worker'));
    }

    /** @return object */
    private function customQueueFactory()
    {
        return new class() extends QueueFactory {
            public function __construct()
            {
                // Drupal 11's QueueFactory::__construct() takes a second,
                // required $container parameter (which it promotes directly
                // to the $container property itself); Drupal 9/10's takes
                // only $settings. Reflect on the parent's own constructor
                // rather than branching on \Drupal::VERSION, since it's the
                // parameter count itself that determines whether the call
                // below is even valid. $this->container is still set
                // manually afterwards regardless - harmless on 11 (just
                // reassigns the same value the parent constructor already
                // set), necessary on 9/10 (which never set it at all).
                if ((new \ReflectionMethod(QueueFactory::class, '__construct'))->getNumberOfParameters() >= 2) {
                    /** @phpstan-ignore-next-line */
                    parent::__construct(Settings::getInstance(), \Drupal::getContainer());
                } else {
                    /** @phpstan-ignore-next-line */
                    parent::__construct(Settings::getInstance());
                }

                /** @phpstan-ignore-next-line */
                $this->container = \Drupal::getContainer();
            }

            public function get($name, $reliable = false): QueueInterface
            {
                if ($name == 'create_node_worker' && $reliable) {
                    /** @phpstan-ignore-next-line */
                    $reliableQueueService = $this->container->get('queue_reliable_service_create_node_worker');

                    /** @phpstan-ignore-next-line */
                    $this->queues[$name] = $reliableQueueService->get($name);

                    return $this->queues[$name];
                }

                return parent::get($name, $reliable);
            }
        };
    }
}
