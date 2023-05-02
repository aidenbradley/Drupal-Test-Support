<?php

namespace Drupal\Tests\test_support\Kernel\Support;

use Drupal\KernelTests\KernelTestBase;
use AidenBradley\DrupalTestSupport\Support\Exceptions\CronFailed;
use AidenBradley\DrupalTestSupport\Support\InteractsWithCron;

class InteractsWithCronTest extends KernelTestBase
{
    use InteractsWithCron;

    /** @var string[] */
    protected static $modules = [
        'test_support_cron',
    ];

    /** @test */
    public function set_cron_key(): void
    {
        $this->assertNull($this->getCronKey());

        $this->setCronKey('EXAMPLE_CRON_KEY');

        $this->assertEquals('EXAMPLE_CRON_KEY', $this->getCronKey());
    }

    /** @test */
    public function running_system_cron_requires_cron_key(): void
    {
        $this->expectException(CronFailed::class);
        $this->expectExceptionCode(CronFailed::NO_CRON_KEY_SET);

        $this->assertNull($this->getCronKey());

        $this->runSystemCron();
    }

    /** @test */
    public function run_cron(): void
    {
        $this->setCronKey('EXAMPLE_CRON_KEY');

        $this->assertNull(
            $this->container->get('state')->get('CRON_TRIGGERED')
        );

        $this->runSystemCron();

        $this->assertTrue(
            $this->container->get('state')->get('CRON_TRIGGERED')
        );
    }

    /** @test */
    public function run_cron_multiple(): void
    {
        $this->setCronKey('EXAMPLE_CRON_KEY');

        $this->assertNull(
            $this->container->get('state')->get('CRON_TRIGGERED')
        );

        $this->runSystemCron();

        $this->assertTrue(
            $this->container->get('state')->get('CRON_TRIGGERED')
        );

        $this->container->get('state')->set('CRON_TRIGGERED', null);
        $this->assertNull(
            $this->container->get('state')->get('CRON_TRIGGERED')
        );

        $this->runSystemCron();

        $this->assertTrue(
            $this->container->get('state')->get('CRON_TRIGGERED')
        );
    }
}
