<?php

namespace AidenBradley\DrupalTestSupport\Support;

use Carbon\Carbon;
use Drupal\Component\Datetime\TimeInterface;
use AidenBradley\DrupalTestSupport\Installs\InstallsExportedConfig;
use AidenBradley\DrupalTestSupport\Support\Time\Tardis;
use AidenBradley\DrupalTestSupport\Support\Time\Time;
use Drupal\user\UserInterface;

trait InteractsWithDrupalTime
{
    use InstallsExportedConfig;

    /** @var bool */
    private $setupDateDependencies = false;

    /** @param UserInterface<mixed> $user */
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

        $this->travel()->toTimezone($timezone);

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
