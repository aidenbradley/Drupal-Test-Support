<?php

namespace AidenBradley\DrupalTestSupport\Support\Contracts;

use Illuminate\Support\Collection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

interface TestEventDispatcher extends EventDispatcherInterface
{
    public function getFiredEvents(?string $event = null): Collection;
}
