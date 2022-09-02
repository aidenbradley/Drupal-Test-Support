<?php

namespace Drupal\Tests\test_support\Traits\Support;

use Drupal\Core\Url;
use Drupal\Tests\test_support\Traits\Http\MakesHttpRequests;
use Drupal\Tests\test_support\Traits\Support\Exceptions\CronFailed;

trait InteractsWithCron
{
    use MakesHttpRequests;

    /** @var bool */
    private $setupCronDependencies = false;

    public function setCronKey(string $cronKey): self
    {
        $this->container->get('state')->set('system.cron_key', $cronKey);

        return $this;
    }

    public function getCronKey(): ?string
    {
        return $this->container->get('state')->get('system.cron_key');
    }

    public function runSystemCron(): self
    {
        if ($this->getCronKey() === null) {
            throw CronFailed::noCronKey();
        }

        $this->setupCronDependencies();

        $cronUrl = Url::fromRoute('system.cron', [
            'key' => $this->getCronKey(),
        ])->toString();

        $this->get($cronUrl);

        return $this;
    }

    private function setupCronDependencies(): void
    {
        if ($this->setupCronDependencies) {
            return;
        }

        $this->enableModules([
            'system',
        ]);

        $this->setupCronDependencies = true;
    }
}
