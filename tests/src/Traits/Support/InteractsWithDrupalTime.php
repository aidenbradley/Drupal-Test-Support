<?php

namespace Drupal\Tests\test_support\Traits\Support;

use Carbon\Carbon;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Tests\test_support\Traits\Installs\InstallsExportedConfig;
use Drupal\Tests\test_support\Traits\Support\Time\Tardis;
use Drupal\Tests\test_support\Traits\Support\Time\Time;
use Drupal\user\UserInterface;

/** Useful test trait if you are using nesbot/caron to handle datetime */
trait InteractsWithDrupalTime
{
    use InstallsExportedConfig;

    /** @var bool */
    private $setupDateDependencies = false;

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

    protected function getDrupalTime(): TimeInterface
    {
        return $this->container->get('datetime.time');
    }

    protected function setSystemDefaultTimezone(string $timezone): self
    {
        $this->setupDateDependencies();

        $this->config('system.date')->set('timezone.default', $timezone)->save();

        return $this;
    }

    private function setDrupalTime(): void
    {
        $this->container->set('datetime.time', Time::fake());
    }

    private function setupDateDependencies(): void
    {
        if ($this->setupDateDependencies === true) {
            return;
        }

        $this->enableModules([
            'system',
        ]);
        $this->installConfig('system');
        $this->installExportedConfig('system.date');

        $this->setupDateDependencies = true;
    }
}
