<?php

namespace Drupal\Tests\test_support\Kernel\Support;

use Carbon\Carbon;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Url;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\test_support\Traits\Http\MakesHttpRequests;
use Drupal\Tests\test_support\Traits\Support\InteractsWithDrupalTime;
use Drupal\user\Entity\User;

class InteractsWithDrupalTimeTest extends KernelTestBase
{
    use InteractsWithDrupalTime,
        MakesHttpRequests;

    protected static $modules = [
        'test_support_time',
    ];

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

        $this->travel()->back();

        $this->assertEquals(time(), $this->time()->getRequestTime());
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
        $this->travelTo('3rd January 2000 15:05:00');

        $this->travel(5)->minutes();

        $this->assertTimeIs('3rd January 2000 15:10:00');
    }

    /** @test */
    public function hours(): void
    {
        $this->travelTo('3rd January 2000 15:00:00');

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
        $this->travelTo('10th January 2000 20:00:00');

        $this->travel(2)->months();

        $this->assertTimeIs('10th March 2000 20:00:00');
    }

    /** @test */
    public function years(): void
    {
        $this->travelTo('10th March 2000 20:00:00');

        $this->travel(2)->years();

        $this->assertTimeIs('10th March 2002 20:00:00');
    }

    /** @test */
    public function freeze_time_travel(): void
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

        $this->assertEquals(time(), $this->time()->getRequestTime());

        $timeTraveller = User::load(10);
        $dateTimeTravellerWasCreated = date('jS F o H:i:s', $timeTraveller->created->value);

        $this->assertEquals('3rd January 2005 15:00:00', $dateTimeTravellerWasCreated);
    }

    /** @test */
    public function travel_to_with_timezone(): void
    {
        $this->travelTo('3rd January 2000 15:00:00', 'Europe/London');
        $this->assertEquals('Europe/London', date_default_timezone_get());

        $this->travelTo('3rd January 2000 15:00:00', 'Europe/Rome');
        $this->assertEquals('Europe/Rome', date_default_timezone_get());
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
    public function freeze_timezone_travel(): void
    {
        $this->enableModules([
            'user',
        ]);
        $this->installEntitySchema('user');

        $this->travelTo('3rd January 2000 15:00:00', 'Europe/London');

        $this->travel()->toTimezone('Europe/Rome', function() {
            User::create([
                'uid' => 10,
                'name' => 'time.traveler',
                'created' => Carbon::now()->timestamp,
            ])->save();
        });

        $this->assertEquals(time(), $this->time()->getRequestTime());

        $timeTraveller = User::load(10);
        $dateTimeTravellerWasCreated = date('jS F o H:i:s', $timeTraveller->created->value);

        $this->assertEquals('3rd January 2000 16:00:00', $dateTimeTravellerWasCreated);
    }

    /** @test */
    public function set_user_timezone(): void
    {
        $this->enableModules([
            'user',
        ]);
        $this->installEntitySchema('user');

        $user = $this->container->get('entity_type.manager')->getStorage('user')->create([
            'uid' => 100,
            'name' => 'user.timezone_test',
        ]);
        $user->save();

        $this->assertNull($user->getTimeZone());

        $this->setUsersTimezone($user, 'Europe/London');

        $user = $this->container->get('entity_type.manager')->getStorage('user')->load(100);

        $this->assertEquals('Europe/London', $user->getTimezone());
    }

    private function assertTimeIs(string $time)
    {
        $drupalTime = $this->container->get('datetime.time');

        $this->assertEquals($time, date('jS F o H:i:s', $drupalTime->getRequestTime()));
        $this->assertEquals($time, date('jS F o H:i:s', $drupalTime->getCurrentTime()));
    }

    private function route(string $routeName, array $parameters = [], array $options = []): string
    {
        return Url::fromRoute(...func_get_args())->toString(true)->getGeneratedUrl();
    }

    private function time(): TimeInterface
    {
        return $this->container->get('datetime.time');
    }
}
