<?php

namespace Drupal\test_support_queue\Queue;

use Drupal\Core\Queue\QueueInterface;

class ReliableCreateNodeQueue implements QueueInterface
{
    /** @param mixed $data */
    public function createItem($data): void
    {
        // silence is golden
    }

    public function numberOfItems(): int
    {
        return 0;
    }

    /** @param int|mixed $leaseTime */
    public function claimItem($leaseTime = 3600): void
    {
        // silence is golden
    }

    /** @param mixed $item */
    public function deleteItem($item): void
    {
        // silence is golden
    }

    /** @param mixed $item */
    public function releaseItem($item): bool
    {
        return true;
    }

    public function createQueue(): void
    {
        // silence is golden
    }

    public function deleteQueue(): void
    {
        // silence is golden
    }
}
