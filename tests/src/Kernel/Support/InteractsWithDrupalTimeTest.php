<?php

namespace Drupal\Tests\test_support\Kernel\Support;

use Drupal\Component\Utility\Random;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\test_support\Traits\Installs\InstallsExportedConfig;
use Drupal\Tests\test_support\Traits\Support\InteractsWithAuthentication;
use Drupal\Tests\test_support\Traits\Support\InteractsWithDrupalTime;
use Drupal\Tests\test_support\Traits\Support\InteractsWithEntities;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;

class InteractsWithDrupalTimeTest extends KernelTestBase
{
    use InstallsExportedConfig;
    use InteractsWithAuthentication;
    use InteractsWithDrupalTime;
    use InteractsWithEntities;

    /** @var string[] */
    protected static $modules = [
        'system',
        'node',
        'user',
    ];

    private const DATE_FORMAT = 'jS F o H:i:s';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setConfigDirectory(__DIR__ . '/__fixtures__/config/sync/time_travel');

        $this->installEntitySchema('node');
        $this->installEntitySchema('user');

        $this->installSchema('system', 'sequences');

        $this->createEntity('node_type', [
            'type' => 'page',
            'name' => 'Basic page',
        ]);
    }

    /** @test */
    public function travel_to(): void
    {
        $this->travelTo('3rd January 2000 15:00:00');
        $this->assertTimeIs('3rd January 2000 15:00:00');

        $this->travelTo('30th January 2000 15:00:00', 'Europe/London');
        $this->assertTimezoneIs('Europe/London');
        $this->assertTimeIs('30th January 2000 15:00:00');

        $this->travelTo('30th January 2000 18:00:00');
        $this->assertTimezoneIs('Europe/London');
        $this->assertTimeIs('30th January 2000 18:00:00');
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
        $this->travelTo('3rd January 2000 15:00:00', 'Europe/London');
        $this->assertTimezoneIs('Europe/London');

        $this->travel(5)->years(function () {
            $this->createEntity('user', [
                'uid' => 10,
                'name' => 'time.traveler',
                'created' => $this->getDrupalTime()->getCurrentTime(),
            ]);
        });

        $this->assertTimezoneIs('Europe/London');
        $this->assertEquals(time(), $this->getDrupalTime()->getRequestTime());

        $dateTimeTravellerWasCreated = $this->formatDate(
            $this->loadUser(10)->get('created')->getString()
        );

        $this->assertEquals('3rd January 2005 15:00:00', $dateTimeTravellerWasCreated);
    }

    /** @test */
    public function travelling_to_timezone_sets_sysmtes_default_timezone(): void
    {
        $timezone = $this->config('system.date')->get('timezone');
        $this->assertNull($timezone);

        $this->travel()->toTimezone('Europe/Rome');

        /** @var array<string> $timezone */
        $timezone = $this->config('system.date')->get('timezone');

        if (isset($timezone['default']) === false) {
            $this->fail('Timezone does not have default key');
        }

        $this->assertEquals('Europe/Rome', $timezone['default']);

        $this->travel()->toTimezone('Europe/Athens');

        /** @var array<string> $timezone */
        $timezone = $this->config('system.date')->get('timezone');

        $this->assertEquals('Europe/Athens', $timezone['default']);
    }

    /** @test */
    public function travel_to_date_with_timezone(): void
    {
        $this->travelTo('3rd January 2000 15:00:00', 'Europe/London');
        $this->assertTimezoneIs('Europe/London');

        $this->travelTo('3rd January 2000 15:00:00', 'Europe/Rome');
        $this->assertTimezoneIs('Europe/Rome');
    }

    /** @test */
    public function travel_to_timezone(): void
    {
        $this->travelTo('10th January 2020 15:00:00', 'Europe/London');
        $this->assertTimezoneIs('Europe/London');
        $this->assertTimeIs('10th January 2020 15:00:00');

        $this->travel()->toTimezone('Europe/Rome');
        $this->assertTimezoneIs('Europe/Rome');
        $this->assertTimeIs('10th January 2020 16:00:00');

        $this->travel()->toTimezone('Europe/Athens');
        $this->assertTimezoneIs('Europe/Athens');
        $this->assertTimeIs('10th January 2020 17:00:00');

        $this->travel()->toTimezone('Europe/Istanbul');
        $this->assertTimezoneIs('Europe/Istanbul');
        $this->assertTimeIs('10th January 2020 18:00:00');

        $this->travel()->toTimezone('America/Los_Angeles');
        $this->assertTimezoneIs('America/Los_Angeles');
        $this->assertTimeIs('10th January 2020 07:00:00');

        $this->travel()->toTimezone('Europe/London');
        $this->assertTimezoneIs('Europe/London');
        $this->assertTimeIs('10th January 2020 15:00:00');
    }

    /** @test */
    public function freeze_timezone_travel(): void
    {
        $this->travelTo('3rd January 2000 15:00:00', 'Europe/London');
        $this->assertTimezoneIs('Europe/London');

        $this->travel()->toTimezone('Europe/Rome', function () {
            $this->assertTimezoneIs('Europe/Rome');

            $this->createEntity('user', [
                'uid' => 10,
                'name' => 'time.traveler',
                'created' => $this->getDrupalTime()->getCurrentTime(),
            ]);
        });

        $this->assertEquals(time(), $this->getDrupalTime()->getRequestTime());
        $this->assertTimezoneIs('Europe/London');

        $dateTimeTravellerWasCreated = $this->formatDate(
            $this->loadUser(10)->get('created')->getString()
        );
        $this->assertEquals('3rd January 2000 15:00:00', $dateTimeTravellerWasCreated);

        $this->travel()->toTimezone('Europe/Rome');
        $dateTimeTravellerWasCreated = $this->formatDate(
            $this->loadUser(10)->get('created')->getString()
        );
        $this->assertEquals('3rd January 2000 16:00:00', $dateTimeTravellerWasCreated);
    }

    /** @test */
    public function set_user_timezone(): void
    {
        /** @var UserInterface<mixed> $user */
        $user = $this->createEntity('user', [
            'uid' => 100,
            'name' => 'user.timezone_test',
        ]);

        /**
         * The return type on getTimeZone can return null
         *
         * @phpstan-ignore-next-line
         */
        $this->assertNull($user->getTimeZone());

        $this->setUsersTimezone($user, 'Europe/London');

        $this->assertEquals('Europe/London', $user->getTimezone());
    }

    /** @test */
    public function date_formatter_service_adheres_to_timezones(): void
    {
        $this->travel()->toTimezone('Europe/London');
        $this->assertTimezoneIs('Europe/London');
        $this->travelTo('5th February 2022 15:00:00');

        /** @var \Drupal\node\Entity\Node $node */
        $node = $this->createEntity('node', [
            'type' => 'page',
            'title' => 'node created on 5th February 2022 at 15:00:00 London time',
            'created' => $this->getDrupalTime()->getRequestTime(),
        ]);
        $this->assertEquals('5th February 2022 15:00:00', $this->formatDate($node->get('created')->getString()));

        $this->travel()->toTimezone('Europe/Rome');
        $this->assertTimezoneIs('Europe/Rome');
        $this->assertEquals('5th February 2022 16:00:00', $this->formatDate($node->get('created')->getString()));

        $this->travel()->toTimezone('Europe/Athens');
        $this->assertTimezoneIs('Europe/Athens');
        $this->assertEquals('5th February 2022 17:00:00', $this->formatDate($node->get('created')->getString()));

        $this->travel()->toTimezone('Europe/Istanbul');
        $this->assertTimezoneIs('Europe/Istanbul');
        $this->assertEquals('5th February 2022 18:00:00', $this->formatDate($node->get('created')->getString()));
    }

    /** @test */
    public function system_uses_logged_in_users_timezone(): void
    {
        $this->travelTo('3rd January 2000 15:00:00', 'Europe/London');
        $this->assertTimeIs('3rd January 2000 15:00:00');
        $this->assertTimezoneIs('Europe/London');
        $this->assertFalse($this->container->get('current_user')->isAuthenticated());

        $romeUser = $this->createUserWithTimezone('Europe/Rome');
        $this->actingAs($romeUser);
        $this->assertTimeIs('3rd January 2000 16:00:00');
        $this->assertEquals('Europe/Rome', date_default_timezone_get());

        $athensUser = $this->createUserWithTimezone('Europe/Athens');
        $this->actingAs($athensUser);
        $this->assertTimeIs('3rd January 2000 17:00:00');
        $this->assertEquals('Europe/Athens', date_default_timezone_get());

        $moscowUser = $this->createUserWithTimezone('Europe/Moscow');
        $this->actingAs($moscowUser);
        $this->assertTimeIs('3rd January 2000 18:00:00');
        $this->assertEquals('Europe/Moscow', date_default_timezone_get());
    }

    /** @test */
    public function dates_adhere_to_users_timezone(): void
    {
        $this->travelTo('3rd January 2000 15:00:00', 'Europe/London');
        $this->assertTimezoneIs('Europe/London');
        $this->assertTimeIs('3rd January 2000 15:00:00');

        /** @var \Drupal\node\Entity\Node $node */
        $node = $this->createEntity('node', [
            'type' => 'page',
            'title' => 'node created on 3rd January 2000 15:00:00 London time',
            'created' => $this->getDrupalTime()->getRequestTime(),
        ]);

        $romeUser = $this->createUserWithTimezone('Europe/Rome');

        $this->assertEquals('3rd January 2000 15:00:00', $this->formatDate($node->get('created')->getString()));
        $this->actingAs($romeUser);
        $this->assertEquals('3rd January 2000 16:00:00', $this->formatDate($node->get('created')->getString()));
    }

    private function assertTimeIs(string $time): void
    {
        $this->assertEquals($time, $this->formatDate($this->getDrupalTime()->getRequestTime()));
        $this->assertEquals($time, $this->formatDate($this->getDrupalTime()->getCurrentTime()));
    }

    private function assertTimezoneIs(string $timezone): void
    {
        /** @var array<string> $timezoneConfiguration */
        $timezoneConfiguration = $this->config('system.date')->get('timezone');

        $this->assertEquals($timezone, date_default_timezone_get());
        $this->assertEquals($timezone, $timezoneConfiguration['default']);
    }

    /** @param int|string $timestamp */
    private function formatDate($timestamp): string
    {
        return $this->container->get('date.formatter')->format(
            (int) $timestamp,
            'custom',
            self::DATE_FORMAT
        );
    }

    private function createUserWithTimezone(string $timezone): User
    {
        /** @var User $user */
        $user = $this->createEntity('user', [
            'name' => (new Random())->string(),
            'timezone' => $timezone,
        ]);

        return $user;
    }

    /** @return UserInterface<mixed> */
    private function loadUser(int $userId): UserInterface
    {
        $user = $this->container->get('entity_type.manager')->getStorage('user')->load($userId);

        if ($user instanceof UserInterface === false) {
            $this->fail('Failed to load user with ID: ' . $userId);
        }

        return $user;
    }
}
