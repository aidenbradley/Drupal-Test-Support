<?php

namespace Drupal\Tests\test_support\Traits\Support;

use Carbon\Carbon;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Tests\test_support\Traits\Installs\InstallsExportedConfig;
use Drupal\Tests\test_support\Traits\Support\Time\Tardis;
use Drupal\Tests\test_support\Traits\Support\Time\Time;
use Drupal\user\UserInterface;

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
        $this->setupDateDependencies();

        if ($timezone === null) {
            $timezone = Carbon::now()->getTimezone();
        }

        Carbon::setTestNowAndTimezone(
            Carbon::createFromTimeString($date)->shiftTimezone($timezone)
        );

        $this->setSystemDefaultTimezone($timezone);

        return $this;
    }

    protected function travel(?int $travel = null): Tardis
    {
        $this->setupDateDependencies();

        return Tardis::createFromTravel($this->container, $travel);
    }

    protected function getDrupalTime(): TimeInterface
    {
        $this->setupDateDependencies();

        return $this->container->get('datetime.time');
    }

    protected function setSystemDefaultTimezone(string $timezone): self
    {
        $this->setupDateDependencies();

        $this->config('system.date')->set('timezone.default', $timezone)->save();

        $this->travel()->toTimezone($timezone);

        return $this;
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

        $this->container->set('datetime.time', Time::fake());

        $this->setupDateDependencies = true;
    }
}
