<?php

namespace Drupal\Tests\test_support\Kernel\Support;

use Carbon\Carbon;
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

        $content = $this->get($this->route('time.request_time'))->getContent();

        $response = json_decode($content);

        $this->assertEquals('3rd January 2000 15:00:00', date('jS F o H:i:s', $response->request_time));
    }

    /** @test */
    public function back(): void
    {
        $this->travelTo('3rd January 2000 15:00:00');

        $content = $this->get($this->route('time.request_time'))->getContent();
        $response = json_decode($content);
        $this->assertEquals('3rd January 2000 15:00:00', date('jS F o H:i:s', $response->request_time));

        $this->travel()->back();

        $content = $this->get($this->route('time.request_time'))->getContent();
        $response = json_decode($content);
        $this->assertEquals(time(), $response->request_time);
    }

    /** @test */
    public function seconds(): void
    {
        Carbon::setTestNow('3rd January 2000 15:00:00');

        $this->travel(5)->seconds();

        $content = $this->get($this->route('time.request_time'))->getContent();
        $response = json_decode($content);
        $this->assertEquals('3rd January 2000 15:00:05', date('jS F o H:i:s', $response->request_time));
    }

    /** @test */
    public function minutes(): void
    {
        Carbon::setTestNow('3rd January 2000 15:05:00');

        $this->travel(5)->minutes();

        $content = $this->get($this->route('time.request_time'))->getContent();
        $response = json_decode($content);
        $this->assertEquals('3rd January 2000 15:10:00', date('jS F o H:i:s', $response->request_time));
    }

    /** @test */
    public function hours(): void
    {
        Carbon::setTestNow('3rd January 2000 15:00:00');

        $this->travel(5)->hours();

        $content = $this->get($this->route('time.request_time'))->getContent();
        $response = json_decode($content);
        $this->assertEquals('3rd January 2000 20:00:00', date('jS F o H:i:s', $response->request_time));
    }

    /** @test */
    public function days(): void
    {
        Carbon::setTestNow('3rd January 2000 20:00:00');

        $this->travel(5)->days();

        $content = $this->get($this->route('time.request_time'))->getContent();
        $response = json_decode($content);
        $this->assertEquals('8th January 2000 20:00:00', date('jS F o H:i:s', $response->request_time));
    }

    /** @test */
    public function weeks(): void
    {
        Carbon::setTestNow('10th January 2000 20:00:00');

        $this->travel(2)->weeks();

        $content = $this->get($this->route('time.request_time'))->getContent();
        $response = json_decode($content);
        $this->assertEquals('24th January 2000 20:00:00', date('jS F o H:i:s', $response->request_time));
    }

    /** @test */
    public function months(): void
    {
        Carbon::setTestNow('10th January 2000 20:00:00');

        $this->travel(2)->months();

        $content = $this->get($this->route('time.request_time'))->getContent();
        $response = json_decode($content);
        $this->assertEquals('10th March 2000 20:00:00', date('jS F o H:i:s', $response->request_time));
    }

    /** @test */
    public function years(): void
    {
        Carbon::setTestNow('10th March 2000 20:00:00');

        $this->travel(2)->years();

        $content = $this->get($this->route('time.request_time'))->getContent();
        $response = json_decode($content);
        $this->assertEquals('10th March 2002 20:00:00', date('jS F o H:i:s', $response->request_time));
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

        $content = $this->get($this->route('time.request_time'))->getContent();
        $response = json_decode($content);
        $this->assertEquals(time(), $response->request_time);

        $timeTraveller = User::load(10);

        $this->assertEquals('3rd January 2005 15:00:00', date('jS F o H:i:s', $timeTraveller->created->value));
    }

    /** @test */
    public function travel_to_with_timezone(): void
    {
        $this->travelTo('3rd January 2000 15:00:00', 'Europe/London');

        $this->assertEquals('Europe/London', date_default_timezone_get());
        $this->assertEquals('Europe/London', Carbon::now()->getTimezone());

        $this->travelTo('3rd January 2000 15:00:00', 'Europe/Rome');

        $this->assertEquals('Europe/Rome', date_default_timezone_get());
        $this->assertEquals('Europe/Rome', Carbon::now()->getTimezone());
    }

    /** @test */
    public function travel_to_timezone(): void
    {
        $this->travelTo('3rd January 2000 15:00:00', 'Europe/London');

        $content = $this->get($this->route('time.request_time'))->getContent();
        $response = json_decode($content);
        $this->assertEquals('3rd January 2000 15:00:00', date('jS F o H:i:s', $response->request_time));

        $this->travel()->toTimezone('Europe/Rome');

        $content = $this->get($this->route('time.request_time'))->getContent();
        $response = json_decode($content);
        $this->assertEquals('3rd January 2000 16:00:00', date('jS F o H:i:s', $response->request_time));

        $this->travel()->toTimezone('Europe/Athens');

        $content = $this->get($this->route('time.request_time'))->getContent();
        $response = json_decode($content);
        $this->assertEquals('3rd January 2000 17:00:00', date('jS F o H:i:s', $response->request_time));

        $this->travel()->toTimezone('America/Los_Angeles');

        $content = $this->get($this->route('time.request_time'))->getContent();
        $response = json_decode($content);
        $this->assertEquals('3rd January 2000 07:00:00', date('jS F o H:i:s', $response->request_time));

        $this->travel()->toTimezone('Europe/London');

        $content = $this->get($this->route('time.request_time'))->getContent();
        $response = json_decode($content);
        $this->assertEquals('3rd January 2000 15:00:00', date('jS F o H:i:s', $response->request_time));
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

        $content = $this->get($this->route('time.request_time'))->getContent();
        $response = json_decode($content);
        $this->assertEquals(time(), $response->request_time);

        $timeTraveller = User::load(10);

        $this->assertEquals('3rd January 2000 16:00:00', date('jS F o H:i:s', $timeTraveller->created->value));
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
}
