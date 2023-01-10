<?php

namespace Drupal\Tests\test_support\Traits\Support;

use Carbon\Carbon;
use Drupal\Tests\test_support\Traits\Support\Time\Tardis;
use Drupal\Tests\test_support\Traits\Support\Time\Time;
use Drupal\user\UserInterface;

/** Useful test trait if you are using nesbot/caron to handle datetime */
trait InteractsWithDrupalTime
{
    protected function setUsersTimezone(UserInterface $user, string $timezone): self
    {
        $user->set('timezone', $timezone);
        $user->save();

        return $this;
    }

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
