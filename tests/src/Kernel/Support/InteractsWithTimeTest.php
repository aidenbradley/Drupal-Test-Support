<?php

namespace Drupal\Tests\test_support\Kernel\Support;

use Carbon\Carbon;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\test_support\Traits\Support\InteractsWithTime;
use Drupal\user\Entity\User;

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
    public function travel_to_with_timezone(): void
    {
        $this->travelTo('3rd January 2000 15:00:00', 'Europe/London');

        $this->assertEquals('Europe/London', Carbon::now()->getTimezone());

        $this->travelTo('3rd January 2000 15:00:00', 'Europe/Rome');

        $this->assertEquals('Europe/Rome', Carbon::now()->getTimezone());
    }

    /** @test */
    public function travel_to_timezone(): void
    {
        $this->travelTo('3rd January 2000 15:00:00', 'Europe/London');

        $this->assertTimeIs('3rd January 2000 15:00:00');

        $this->travel()->toTimezone('Europe/Rome');
        $this->assertTimeIs('3rd January 2000 16:00:00');

        $this->travel()->toTimezone('Europe/Athens');
        $this->assertTimeIs('3rd January 2000 17:00:00');

        $this->travel()->toTimezone('America/Los_Angeles');
        $this->assertTimeIs('3rd January 2000 07:00:00');

        $this->travel()->toTimezone('Europe/London');
        $this->assertTimeIs('3rd January 2000 15:00:00');
    }

    /** @test */
    public function back(): void
    {
        $this->travelTo('3rd January 2000 15:00:00');

        $this->assertTimeIs('3rd January 2000 15:00:00');

        $this->travel()->back();

        $this->assertEquals(time(), Carbon::now()->timestamp);
    }

    /** @test */
    public function seconds(): void
    {
        Carbon::setTestNow('3rd January 2000 15:00:00');

        $this->travel(5)->seconds();

        $this->assertTimeIs('3rd January 2000 15:00:05');
    }

    /** @test */
    public function minutes(): void
    {
        Carbon::setTestNow('3rd January 2000 15:05:00');

        $this->travel(5)->minutes();

        $this->assertTimeIs('3rd January 2000 15:10:00');
    }

    /** @test */
    public function hours(): void
    {
        Carbon::setTestNow('3rd January 2000 15:00:00');

        $this->travel(5)->hours();

        $this->assertTimeIs('3rd January 2000 20:00:00');
    }

    /** @test */
    public function days(): void
    {
        Carbon::setTestNow('3rd January 2000 20:00:00');

        $this->travel(5)->days();

        $this->assertTimeIs('8th January 2000 20:00:00');
    }

    /** @test */
    public function weeks(): void
    {
        Carbon::setTestNow('10th January 2000 20:00:00');

        $this->travel(2)->weeks();

        $this->assertTimeIs('24th January 2000 20:00:00');
    }

    /** @test */
    public function months(): void
    {
        Carbon::setTestNow('10th January 2000 20:00:00');

        $this->travel(2)->months();

        $this->assertTimeIs('10th March 2000 20:00:00');
    }

    /** @test */
    public function years(): void
    {
        Carbon::setTestNow('10th March 2000 20:00:00');

        $this->travel(2)->years();

        $this->assertTimeIs('10th March 2002 20:00:00');
    }

    /** @test */
    public function closure_time_travel(): void
    {
        $this->enableModules([
            'user',
        ]);
        $this->installEntitySchema('user');

        $this->travelTo('3rd January 2000 15:00:00');

        $this->travel(5)->years(function() {
            User::create([
                'uid' => 10,
                'name' => 'time.traveler',
                'created' => Carbon::now()->timestamp,
            ])->save();
        });

        $this->assertEquals(Carbon::now()->timestamp, time());

        $timeTraveller = User::load(10);

        $this->assertEquals(
            Carbon::createFromTimeString('3rd January 2005 15:00:00')->timestamp,
            Carbon::createFromTimestamp($timeTraveller->created->value)->timestamp
        );
    }

    private function assertTimeIs(string $time)
    {
        $this->assertEquals($time, Carbon::now()->format('jS F o H:i:s'));
    }
}
