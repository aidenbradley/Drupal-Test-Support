<?php

namespace Drupal\Tests\test_support\Kernel\Support;

use Carbon\Carbon;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\test_support\Traits\Support\InteractsWithTime;

class InteractsWithTimeTest extends KernelTestBase
{
    use InteractsWithTime;

    /** @test */
    public function seconds(): void
    {
//        Carbon::setTestNowAndTimezone();
        Carbon::setTestNow('1st January 2000 15:00:00');
        dump(Carbon::now()->format('jS F o H:i:s'));
        $this->timeTravel(5)->seconds();
        dump(Carbon::now()->format('jS F o H:i:s'));

        $this->assertTimeIs('1st January 2000 15:00:05');
    }

    private function assertTimeIs(string $time)
    {
        $this->assertEquals($time, Carbon::now()->format('jS F o H:i:s'));
    }
}
