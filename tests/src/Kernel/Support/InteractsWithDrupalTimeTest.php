<?php

namespace Drupal\Tests\test_support\Kernel\Support;

use Carbon\Carbon;
use Drupal\Core\Url;
use Drupal\KernelTests\KernelTestBase;

use Drupal\Tests\test_support\Traits\Support\InteractsWithAuthentication;
use Drupal\Tests\test_support\Traits\Support\InteractsWithDrupalTime;
use Drupal\Tests\test_support\Traits\Support\InteractsWithEntities;

class InteractsWithDrupalTimeTest extends KernelTestBase
{
    use InteractsWithDrupalTime,
        InteractsWithAuthentication,
        InteractsWithEntities;

    private const DATE_FORMAT = 'jS F o H:i:s';

    protected static $modules = [
        'test_support_time',
    ];

    /** @test */
    public function set_system_default_timezone(): void
    {
        $this->assertNull($this->config('system.date')->get('timezone'));

        $this->assertFalse(isset($this->config('system.date')->get('timezone')['default']));

        $this->setSystemDefaultTimezone('Europe/Rome');
        $this->assertEquals('Europe/Rome', date_default_timezone_get());

        $this->assertEquals('Europe/Rome', $this->config('system.date')->get('timezone')['default']);
    }

    /** @test */
    public function set_system_timezone_and_travel(): void
    {
        $this->travelTo('3rd January 2000 15:00:00', 'Europe/London');
        $this->assertTimeIs('3rd January 2000 15:00:00');

        $this->setSystemDefaultTimezone('Europe/Rome');
        $this->assertEquals('Europe/Rome', date_default_timezone_get());
        $this->assertTimeIs('3rd January 2000 16:00:00');

        $this->travelTo('3rd January 2000 17:00:00', 'Europe/Athens');
        $this->assertTimeIs('3rd January 2000 17:00:00');

        $this->setSystemDefaultTimezone('Europe/Istanbul');
        $this->assertEquals('Europe/Istanbul', date_default_timezone_get());
        $this->assertTimeIs('3rd January 2000 18:00:00');
    }

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

        $this->assertEquals(time(), $this->getDrupalTime()->getRequestTime());
    }

    /** @test */
    public function seconds(): void
    {
        $this->travelTo('3rd January 2000 15:00:00');

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
        $this->travelTo('3rd January 2000 20:00:00');

        $this->travel(5)->days();

        $this->assertTimeIs('8th January 2000 20:00:00');
    }

    /** @test */
    public function weeks(): void
    {
        $this->travelTo('10th January 2000 20:00:00');

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
            $this->createEntity('user', [
                'uid' => 10,
                'name' => 'time.traveler',
                'created' => Carbon::now()->timestamp,
            ]);
        });

        $this->assertEquals(time(), $this->getDrupalTime()->getRequestTime());

        $dateTimeTravellerWasCreated = $this->formatDate(
            $this->storage('user')->load(10)->created->value
        );

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
        $this->travelTo('10th January 2020 15:00:00', 'Europe/London');
        $this->assertTimeIs('10th January 2020 15:00:00');

        $this->travel()->toTimezone('Europe/Rome');
        $this->assertTimeIs('10th January 2020 16:00:00');

        $this->travel()->toTimezone('Europe/Athens');
        $this->assertTimeIs('10th January 2020 17:00:00');

        $this->travel()->toTimezone('Europe/Istanbul');
        $this->assertTimeIs('10th January 2020 18:00:00');

        $this->travel()->toTimezone('America/Los_Angeles');
        $this->assertTimeIs('10th January 2020 07:00:00');

        $this->travel()->toTimezone('Europe/London');
        $this->assertTimeIs('10th January 2020 15:00:00');
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
            $this->createEntity('user', [
                'uid' => 10,
                'name' => 'time.traveler',
                'created' => Carbon::now()->timestamp,
            ]);
        });

        $this->assertEquals(time(), $this->getDrupalTime()->getRequestTime());

        $dateTimeTravellerWasCreated = $this->formatDate(
            $this->storage('user')->load(10)->created->value
        );

        $this->assertEquals('3rd January 2000 16:00:00', $dateTimeTravellerWasCreated);
    }

    /** @test */
    public function set_user_timezone(): void
    {
        $this->enableModules([
            'user',
        ]);
        $this->installEntitySchema('user');

        $user = $this->createEntity('user', [
            'uid' => 100,
            'name' => 'user.timezone_test',
        ]);

        $this->assertNull($user->getTimeZone());

        $this->setUsersTimezone($user, 'Europe/London');

        $this->assertEquals('Europe/London', $user->getTimezone());
    }

    /** @test */
    public function correctly_rendered_dates_adhere_to_system_timezone(): void
    {
        $this->enableModules([
            'system',
            'node',
            'user',
        ]);
        $this->installEntitySchema('node');
        $this->installEntitySchema('user');

        $this->travelTo('3rd January 2000 15:00:00', 'Europe/London');

        $node = $this->createEntity('node', [
            'type' => 'page',
            'title' => 'node created on 1st January 2000 15:00 London time',
            'created' => $this->getDrupalTime()->getRequestTime(),
        ]);

        $nodeCreatedDateFormatted = $this->container->get('date.formatter')->format(
            $node->created->value, 'custom', self::DATE_FORMAT
        );
        $this->assertEquals('3rd January 2000 15:00:00', $nodeCreatedDateFormatted);

        $this->setSystemDefaultTimezone('Europe/Rome');
        $this->assertEquals('Europe/Rome', date_default_timezone_get());

        $nodeCreatedDateFormatted = $this->container->get('date.formatter')->format(
            $node->created->value, 'custom', self::DATE_FORMAT
        );
        $this->assertEquals('3rd January 2000 16:00:00', $nodeCreatedDateFormatted);

        $this->setSystemDefaultTimezone('Europe/Athens');
        $this->assertEquals('Europe/Athens', date_default_timezone_get());

        $nodeCreatedDateFormatted = $this->container->get('date.formatter')->format(
            $node->created->value, 'custom', self::DATE_FORMAT
        );
        $this->assertEquals('3rd January 2000 17:00:00', $nodeCreatedDateFormatted);
    }

    /** @test */
    public function correctly_rendered_dates_adhere_to_user_timezone(): void
    {
        $this->enableModules([
            'system',
            'user',
            'node',
        ]);

        $this->installEntitySchema('user');
        $this->installEntitySchema('node');

        $this->createEntity('node_type', [
            'type' => 'page',
            'name' => 'Basic page',
        ]);

        $this->travelTo('3rd January 2000 15:00:00', 'Europe/London');
        $this->assertTimeIs('3rd January 2000 15:00:00');

        $node = $this->createEntity('node', [
            'type' => 'page',
            'title' => 'node created on 1st January 2000 15:00 London time',
            'created' => $this->getDrupalTime()->getRequestTime(),
        ]);

        $userInRomeTimezone = $this->createEntity('user', [
            'uid' => 100,
            'name' => 'user.timezone_test',
            'timezone' => 'Europe/Rome',
        ]);

        $nodeFormattedCreatedDate = $this->formatDateInUsersTimezone($node->created->value);
        $this->assertEquals('3rd January 2000 15:00:00', $nodeFormattedCreatedDate);

        $this->actingAs($userInRomeTimezone);

        $nodeFormattedCreatedDate = $this->formatDateInUsersTimezone($node->created->value);
        $this->assertEquals('3rd January 2000 16:00:00', $nodeFormattedCreatedDate);
    }

    private function assertTimeIs(string $time)
    {
        $this->assertEquals($time, $this->formatDate($this->getDrupalTime()->getRequestTime()));
        $this->assertEquals($time, $this->formatDate($this->getDrupalTime()->getCurrentTime()));
    }

    private function route(string $routeName, array $parameters = [], array $options = []): string
    {
        return Url::fromRoute(...func_get_args())->toString(true)->getGeneratedUrl();
    }

    private function formatDate(int $timestamp, string $format = self::DATE_FORMAT): string
    {
        return date($format, $timestamp);
    }

    private function formatDateInUsersTimezone(int $timestamp, string $format = self::DATE_FORMAT): string
    {
        return $this->container->get('date.formatter')->format(
            $timestamp, 'custom', $format, $this->container->get('current_user')->getTimezone()
        );
    }
}
