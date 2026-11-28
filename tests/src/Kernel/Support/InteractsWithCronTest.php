<?php

namespace Drupal\Tests\test_support\Kernel\Support;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\test_support\Traits\Support\Exceptions\CronFailed;
use Drupal\Tests\test_support\Traits\Support\InteractsWithCron;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

#[RunTestsInSeparateProcesses]
class InteractsWithCronTest extends KernelTestBase
{
    use InteractsWithCron;

    /** @var string[] */
    protected static $modules = [
        'test_support_cron',
    ];

    /** @test */
    #[Test]
    public function set_cron_key(): void
    {
        $this->assertNull($this->getCronKey());

        $this->setCronKey('EXAMPLE_CRON_KEY');

        $this->assertEquals('EXAMPLE_CRON_KEY', $this->getCronKey());
    }

    /** @test */
    #[Test]
    public function running_system_cron_requires_cron_key(): void
    {
        $this->expectException(CronFailed::class);
        $this->expectExceptionCode(CronFailed::NO_CRON_KEY_SET);

        $this->assertNull($this->getCronKey());

        $this->runSystemCron();
    }

    /** @test */
    #[Test]
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
    #[Test]
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
