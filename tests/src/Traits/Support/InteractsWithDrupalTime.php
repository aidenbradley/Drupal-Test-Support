<?php

namespace Drupal\Tests\test_support\Traits\Support;

use Carbon\Carbon;
use Drupal\Tests\test_support\Traits\Support\Time\Tardis;
use Drupal\Tests\test_support\Traits\Support\Time\Time;

/** Useful test trait if you are using nesbot/caron to handle datetime */
trait InteractsWithDrupalTime
{
    protected function travelTo(string $date, ?string $timezone = null): self
    {
        if ($timezone === null) {
            $timezone = Carbon::now()->getTimezone();
        }

        Carbon::setTestNowAndTimezone(
            Carbon::createFromTimeString($date)->shiftTimezone($timezone)
        );

        $this->setDrupalTime();

        return $this;
    }

    protected function travel(?int $travel = null): Tardis
    {
        $this->setDrupalTime();

        return Tardis::createFromTravel($travel);
    }

    private function setDrupalTime(): void
    {
        $this->container->set('datetime.time', Time::fake());
    }
}
