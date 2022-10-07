<?php

namespace Drupal\Tests\test_support\Traits\Support;

use Carbon\Carbon;
use Drupal\Tests\test_support\Traits\Support\Time\Tardis;

trait InteractsWithTime
{
    protected function travelTo(string $date, ?string $timezone = null): self
    {
        if ($timezone === null) {
            $timezone = Carbon::now()->getTimezone();
        }

        Carbon::setTestNowAndTimezone(
            Carbon::createFromTimeString($date)->shiftTimezone($timezone)
        );

        return $this;
    }

    protected function travel(?int $travel = null): Tardis
    {
        return Tardis::createFromTravel($travel);
    }
}
