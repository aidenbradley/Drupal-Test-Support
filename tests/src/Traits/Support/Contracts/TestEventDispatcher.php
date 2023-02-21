<?php

namespace Drupal\Tests\test_support\Traits\Support\Contracts;

use Illuminate\Support\Collection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

interface TestEventDispatcher extends EventDispatcherInterface
{
    public function getFiredEvents(?string $event = null): Collection;
}
