<?php

namespace Drupal\Tests\test_support\Kernel\Support;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\test_support\Traits\Support\InteractsWithCron;

class InteractsWithCronTest extends KernelTestBase
{
    use InteractsWithCron;

    /** @test */
    public function set_cron_key(): void
    {
        $this->assertNull($this->getCronKey());

        $this->setCronKey('EXAMPLE_CRON_KEY');

        $this->assertEquals('EXAMPLE_CRON_KEY', $this->getCronKey());
    }

    /** @test */
    public function run_cron(): void
    {
        $this->enableModules([
            'test_support_cron',
        ]);

        $this->setCronKey('EXAMPLE_CRON_KEY');

        $this->assertNull(
            $this->container->get('state')->get('CRON_TRIGGERED')
        );

        $this->runSystemCron();

        $this->assertTrue(
            $this->container->get('state')->get('CRON_TRIGGERED')
        );
    }
}
