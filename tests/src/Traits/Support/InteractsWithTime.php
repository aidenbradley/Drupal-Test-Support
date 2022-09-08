<?php

namespace Drupal\Tests\test_support\Traits\Support;

use Carbon\Carbon;
use Drupal\Tests\test_support\Traits\Support\Time\Tardis;

trait InteractsWithTime
{
    protected function travelTo(string $date): self
    {
        Carbon::setTestNow($date);

        return $this;
    }

    protected function timeTravel(int $travel): Tardis
    {
        return Tardis::createFromTravel($travel);
    }
}
