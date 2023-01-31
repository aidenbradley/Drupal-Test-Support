<?php

namespace Drupal\test_support_queue\Factory;

use Drupal\test_support_queue\Queue\ReliableCreateNodeQueue;

class QueueReliableCreateNodeFactory
{
    public function get(string $name) {
        return ReliableCreateNodeQueue::create($name);
    }
}
