<?php

namespace Drupal\Tests\test_support\Kernel\Support;

use Carbon\Carbon;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\test_support\Traits\Support\InteractsWithTime;

class InteractsWithTimeTest extends KernelTestBase
{
    use InteractsWithTime;

    /** @test */
    public function travel_to(): void
    {
        $this->travelTo('3rd January 2000 15:00:00');

        $this->assertTimeIs('3rd January 2000 15:00:00');
    }

    /** @test */
    public function back(): void
    {
        $this->travelTo('3rd January 2000 15:00:00');

        $this->assertTimeIs('3rd January 2000 15:00:00');

        $this->timeTravel()->back();

        $this->assertEquals(time(), Carbon::now()->timestamp);
    }

    /** @test */
    public function seconds(): void
    {
        Carbon::setTestNow('3rd January 2000 15:00:00');

        $this->timeTravel(5)->seconds();

        $this->assertTimeIs('3rd January 2000 15:00:05');
    }

    /** @test */
    public function minutes(): void
    {
        Carbon::setTestNow('3rd January 2000 15:05:00');

        $this->timeTravel(5)->minutes();

        $this->assertTimeIs('3rd January 2000 15:10:00');
    }

    /** @test */
    public function hours(): void
    {
        Carbon::setTestNow('3rd January 2000 15:00:00');

        $this->timeTravel(5)->hours();

        $this->assertTimeIs('3rd January 2000 20:00:00');
    }

    /** @test */
    public function days(): void
    {
        Carbon::setTestNow('3rd January 2000 20:00:00');

        $this->timeTravel(5)->days();

        $this->assertTimeIs('8th January 2000 20:00:00');
    }

    /** @test */
    public function weeks(): void
    {
        Carbon::setTestNow('10th January 2000 20:00:00');

        $this->timeTravel(2)->weeks();

        $this->assertTimeIs('24th January 2000 20:00:00');
    }

    /** @test */
    public function months(): void
    {
        Carbon::setTestNow('10th January 2000 20:00:00');

        $this->timeTravel(2)->months();

        $this->assertTimeIs('10th March 2000 20:00:00');
    }

    /** @test */
    public function years(): void
    {
        Carbon::setTestNow('10th March 2000 20:00:00');

        $this->timeTravel(2)->years();

        $this->assertTimeIs('10th March 2002 20:00:00');
    }

    private function assertTimeIs(string $time)
    {
        $this->assertEquals($time, Carbon::now()->format('jS F o H:i:s'));
    }
}
