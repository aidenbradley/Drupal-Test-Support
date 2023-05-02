<?php

namespace AidenBradley\DrupalTestSupport\Support;

use Drupal\Core\Queue\QueueInterface;

/** To be used in kernel tests */
trait InteractsWithQueues
{
    /** @var QueueInterface[] */
    private $queues = [];

    /** @var bool */
    private $useReliableQueue = true;

    /** @var bool */
    private $dontUseReliableQueue = false;

    public function getQueue(string $queueName): QueueInterface
    {
        return $this->getQueueByName($queueName, $this->dontUseReliableQueue);
    }

    public function getReliableQueue(string $queueName): QueueInterface
    {
        return $this->getQueueByName($queueName, $this->useReliableQueue);
    }

    /** @param mixed $data */
    public function addToQueue(string $queueName, $data): self
    {
        $this->getQueue($queueName)->createItem($data);

        return $this;
    }

    public function processQueue(string $queueName): void
    {
        $queue = $this->getQueue($queueName);

        $queueWorker = $this->container->get('plugin.manager.queue_worker')->createInstance($queueName);

        while ($item = $queue->claimItem()) {
            if ($item instanceof \stdClass === false) {
                return;
            }

            $queueWorker->processItem($item->data);

            $queue->deleteItem($item);
        }
    }

    public function clearQueue(string $queueName): self
    {
        $this->container->get('database')
            ->delete('queue')
            ->condition('name', $queueName)
            ->execute();

        return $this;
    }

    public function getQueueCount(string $queueName): int
    {
        return $this->getQueue($queueName)->numberOfItems();
    }

    private function getQueueByName(string $queueName, bool $useReliableQueue): QueueInterface
    {
        if (isset($this->queues[$queueName])) {
            return $this->queues[$queueName];
        }

        $this->queues[$queueName] = $this->container->get('queue')->get($queueName, $useReliableQueue);

        return $this->queues[$queueName];
    }
}
