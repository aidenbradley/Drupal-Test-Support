[Introduction](#introduction)

[Interacts With Authentication](#interacts-with-authentication)
- [Logging in as a user](#logging-in-as-a-user)
- [Acting as an anonymous user](#acting-as-an-anonymous-user)
- [Acting as user with a single role](#acting-as-user-with-a-single-role)
- [Acting as user with multiple roles](#acting-as-user-with-multiple-roles)

[Interacts With Batches](#interacts-with-batches)
- [Running batches in tests](#interacts-with-batches)

[Interacts With Container](#interacts-with-container)
- [Resolving services out of the container](#resolving-services-out-of-the-container)

[Interacts With Cron](#interacts-with-cron)
- [Setting cron key](#setting-cron-key)
- [Getting cron key](#getting-cron-key)
- [Running cron hooks](#running-cron-hooks)

[Interacting with Drupal Time (Time Travel)](#interacting-with-drupal-time--time-travel-)
- [Time Travel to a date and time](#time-travel-to-a-date-and-time)
- [Time Travel to a date and time to a certain timezone](#time-travel-to-a-date-and-time-to-a-certain-timezone)
- [Time Travel in seconds](#time-travel-in-seconds)
- [Time Travel in minutes](#time-travel-in-minutes)
- [Time Travel in hours](#time-travel-in-hours)
- [Time Travel in days](#time-travel-in-days)
- [Time Travel in weeks](#time-travel-in-weeks)
- [Time Travel in months](#time-travel-in-months)
- [Time Travel in years](#time-travel-in-years)
- [Time Travel to date time and freeze time](#time-travel-to-date-time-and-freeze-time)
- [Time Travel to a timezone](#time-travel-to-a-timezone)
- [Time Travel to a date and time with a timezone](#time-travel-to-a-date-and-time-with-a-timezone)
- [Time Travel to timezone and freeze time](#time-travel-to-timezone-and-freeze-time)
- [Time Travel back to the present](#time-travel-back-to-the-present)

# Introduction
The purpose of the Support API is to provide convenient methods to improve the developer experience when writing automated tests.

There is no single trait for the Support API. Rather there are many traits, where each trait aims to address the developer experience when working with certain areas of Drupal and automated testing.

## Interacts With Authentication
There is a trait called [InteractsWithAuthentication](.././tests/src/Traits/Support/InteractsWithAuthentication.php) that contains an API to improve the developer experience of logging in as a certain user or as a user with certain role(s).

### Logging in as a user
To log in as a certain user in a test, call the `actingAs` method.

```php
public function acting_as(): void
{
    $user = $this->loadUser(50);

    $this->actingAs($user);
}
```

### Acting as an anonymous user
To set the test run to use an anonymous user, call the `actingAsAnonymous` method.

There is no need to create or pass an anonymous user. The `actingAsAnonymous` method will handle this for you.

```php
public function acting_as_anonymous(): void
{
    $this->actingAsAnonymous();
}
```

### Acting as user with a single role
To log in as a user with a certain role set against them, call the `actingAsRole` method.

Under the hood, this method will create a user for you, attribute the given role to the user and log in as them.

```php
public function acting_as_role(): void
{
    $editorRole = $this->loadRole('editor');

    $this->actingAsRole($editorRole);
}
```

### Acting as user with multiple roles
To log in as a user with certain roles set against them, call the `actingAsRoles` method.

Under the hood, this method will create a user for you, attribute the given array of roles to the user and log in as them.

```php
public function acting_as_roles(): void
{
    $writerRole = $this->loadRole('writer');
    $editorRole = $this->loadRole('editor');

    $this->actingAsRoles([
        $writerRole,
        $editorRole,
    ]);
}
```

## Interacts With Batches
There is a trait called [InteractsWithBatches](.././tests/src/Traits/Support/InteractsWithBatches.php) that contains an API to improve the developer experience of running batches.

All that is required by the developer writing the test is to trigger the part of the system that prepares and runs the batch!

### Running batches in tests
To run a batch in a test, first trigger the part of the system that creates and runs the batch. After that, simply tell the test to actually run the batch.

To do this, call the `runLatestBatch` method. This tells the test to run the latest batch that has been created.

In the example below, we will make a `GET` request to a URL that prepares and processes a batch. Then we tell the test to process the batch.

To do this, we will use the two following traits -

- [InteractsWithBatches](.././tests/src/Traits/Support/InteractsWithBatches.php)
- [MakesHttpRequests](.././tests/src/Traits/Support/MakesHttpRequests.php)

```php
use Drupal\Tests\test_support\Traits\Support\InteractsWithBatches
use Drupal\Tests\test_support\Traits\Http\MakesHttpRequests;

public function run_latest_batch(): void
{
    $this->createEnabledUser('enabled_user_one');
    $this->createEnabledUser('enabled_user_two');

    $this->assertEquals(1, $this->loadUser(1)->get('status')->getString());
    $this->assertEquals(1, $this->loadUser(2)->get('status')->getString());

    $batchUrl = $this->getUrlFromRoute('disable_all_users')

    $this->get($batchUrl); //from MakesHttpRequests

    $this->runLatestBatch();

    $this->assertEquals(0, $this->loadUser(1)->get('status')->getString());
    $this->assertEquals(0, $this->loadUser(2)->get('status')->getString());
}
```

## Interacts With Container
There is a trait called [InteractsWithContainer](.././tests/src/Traits/Support/InteractsWithContainer.php) that contains an API to improve the developer experience of interacting with the container.

### Resolving services out of the container
To resolve a service out of the container, call the `service method`.

This method was added because I grew tired of constantly writing `$this->container->get('my_service')`. It's only minor, but now we can do this -

```php
public function get_service(): void
{
    $this->service('my_service');
}
```

## Interacts With Cron
There is a trait called [InteractsWithCron](.././tests/src/Traits/Support/InteractsWithCron.php) that contains an API to improve the developer experience of running cron hooks.

### Setting cron key
Setting the cron key is important if you want to call cron hooks in your test.

To set the cron key, simply call the `setCronKey` method.

```php
public function set_cron_key(): void
{
    $this->setCronKey('my_cron_key');
}
```

### Getting cron key
To get the cron key that has been set, call the `getCronKey` method.

This is useful if you have other routes that validate using the cron key, for example.

```php
public function get_cron_key(): void
{
    $this->setCronKey('my_cron_key');

    $this->getCronKey(); // this will return a string of `my_cron_key`
}
```

### Running cron hooks
To call a cron hook, simply call the `runSystemCron` method.

This method will enable the `system` module and trigger Drupal's cron.

When the `runSystemCron` method is called, it will invoke all `cron` hooks. The cron hooks that are called depened on which modules you have enabled when running your test.

For example, if you have the `comment` module is enabled then the `comment_cron` function will also be triggered.

Under the hood, the `runSystemCron` makes a `GET` request to the cron endpoint, with the cron key, to run Drupal's cron.
```php
public function run_system_cron(): void
{
    $this->runSystemCron();
}
```

## Interacting with Drupal time (Time Travel)
There is a trait called [InteractsWithTime](.././tests/src/Traits/Support/InteractsWithTime.php) that contains an API to improve the developer experience of interacting with time.

This API is particularly useful if you deal with time sensitive operations, such as content that may only be displayed after a certain time (such as the published date).

The trait will -
- Enable the `system` module
- Install the configuration from the `system` module
- Install the `system.date.yml` exported configuration file.
  - This configuration is required in order for this trait to work.

Under the hood, the trait overrides the `datetime.time` service and provides its own service that conforms to the `Drupal\Component\Datetime\TimeInterface`.

### Time Travel to a date and time
To travel to a certain date and time, call the `travelTo` method.

```php
public function travel_to_date_and_time(): void
{
    $this->travelTo('3rd January 2000 15:00:00');
    $this->assertTimeIs('3rd January 2000 15:00:00');
}
```

### Time Travel to a date and time to a certain timezone
To travel to a certain date and time to a certain timezone, call the `travelTo` method and pass a second argument of the timezone, such as `Europe/London`.

```php
public function travel_to(): void
{
    $this->travelTo('30th January 2000 15:00:00', 'Europe/London');
}
```
### Time Travel in seconds
To travel from the current date and time in seconds, call the `travel` method and pass a number. This number correlates to the number of seconds you want to travel.

After that, chain off and call the `seconds` method. This tells the trait you want to travel in seconds.
```php
public function travel_in_seconds(): void
{
    $this->travelTo('3rd January 2000 15:00:00');

    $this->travel(5)->seconds();

    $this->assertTimeIs('3rd January 2000 15:00:05');
}
```
### Time Travel in minutes
To travel from the current date and time in minutes, call the `travel` method and pass a number. This number correlates to the number of minutes you want to travel.

After that, chain off and call the `minutes` method. This tells the trait you want to travel in minutes.
```php
public function travel_in_minutes(): void
{
    $this->travelTo('3rd January 2000 15:05:00');

    $this->travel(5)->minutes();

    $this->assertTimeIs('3rd January 2000 15:10:00');
}
```

### Time Travel in hours
To travel from the current date and time in hours, call the `travel` method and pass a number. This number correlates to the number of hours you want to travel.

After that, chain off and call the `hours` method. This tells the trait you want to travel in hours.

```php
public function travel_in_hours(): void
{
    $this->travelTo('3rd January 2000 15:00:00');

    $this->travel(5)->hours();

    $this->assertTimeIs('3rd January 2000 20:00:00');
}
```

### Time Travel in days
To travel from the current date and time in days, call the `travel` method and pass a number. This number correlates to the number of days you want to travel.

After that, chain off and call the `days` method. This tells the trait you want to travel in days.
```php
public function travel_in_days(): void
{
    $this->travelTo('3rd January 2000 20:00:00');

    $this->travel(5)->days();

    $this->assertTimeIs('8th January 2000 20:00:00');
}
```

### Time Travel in weeks
To travel from the current date and time in weeks, call the `travel` method and pass a number. This number correlates to the number of weeks you want to travel.

After that, chain off and call the `weeks` method. This tells the trait you want to travel in weeks.
```php
public function travel_in_weeks(): void
{
    $this->travelTo('10th January 2000 20:00:00');

    $this->travel(2)->weeks();

    $this->assertTimeIs('24th January 2000 20:00:00');
}
```

### Time Travel in months
To travel from the current date and time in months, call the `travel` method and pass a number. This number correlates to the number of months you want to travel.

After that, chain off and call the `months` method. This tells the trait you want to travel in months.
```php
public function travel_in_months(): void
{
    $this->travelTo('10th January 2000 20:00:00');

    $this->travel(2)->months();

    $this->assertTimeIs('10th March 2000 20:00:00');
}
```

### Time Travel in years
To travel from the current date and time in years, call the `travel` method and pass a number. This number correlates to the number of years you want to travel.

After that, chain off and call the `years` method. This tells the trait you want to travel in years.
```php
public function years(): void
{
    $this->travelTo('10th March 2000 20:00:00');

    $this->travel(2)->years();

    $this->assertTimeIs('10th March 2002 20:00:00');
}
```

### Time Travel to date time and freeze time
To travel and freeze time, pass a callback to the method used to travel.

Once you have finished time travelling, the test will send you back to the present!

Here is an example of travelling 5 years into the future, creating a node and then returning to the present.
```php
public function travel_five_years_freeze_time_and_create_user(): void
{
    $this->travelTo('3rd January 2000 15:00:00', 'Europe/London');

    $this->travel(5)->years(function () {
        $timeTraveler = $this->createEntity('user', [
            'name' => 'time.traveler',
        ]);
    });

    $this->assertEquals(time(), $this->getDrupalTime()->getRequestTime());
}
```

### Time Travel to a date and time in a timezone
To travel to a date and time in a particular timezone, call the `travelTo` method. The method accepts a second optional argument of the timezone you want to travel to.

The timezone set in Drupal is also updated under the hood when you pass the timezone argument.

```php
public function travel_to_date_time_in_timezone(): void
{
    $this->travelTo('10th January 2020 15:00:00', 'Europe/London');

    $this->assertTimezoneIs('Europe/London');
    $this->assertTimeIs('10th January 2020 15:00:00');
}
```

### Time Travel to a timezone
If you want to travel to a different timezone and keep the date and time the same, call the `travel` method and chain off and call the `toTimezone` method.

The timezone set in Drupal is also updated under the hood when you travel to a different timezone.

Here is an example of travelling to a date and time in one timezone and then traveling to a different timezone entirely.

```php
public function travel_to_timezone(): void
{
    $this->travelTo('10th January 2020 15:00:00', 'Europe/London');
    $this->assertTimeIs('10th January 2020 15:00:00');

    $this->travel()->toTimezone('Europe/Rome');
    $this->assertTimeIs('10th January 2020 16:00:00');
}
```

### Time Travel to timezone and freeze time
Just like travelling and freezing time, you can also travel to another timezone and freeze time there.

To do this, pass a callable argument to the `toTimezone` method. Anything inside of this callable will be executed in that timezone. Once completed, you will be returned to the present!

```php
public function travel_and_freeze_timezone(): void
{
    $this->travelTo('3rd January 2000 15:00:00', 'Europe/London');
    $this->assertTimezoneIs('Europe/London');

    $this->travel()->toTimezone('Europe/Rome', function () {
        User::create([
            'name' => 'time.traveler',
        ])->save();

        $this->assertTimezoneIs('Europe/Rome');
    });

    $this->assertEquals(time(), $this->getDrupalTime()->getRequestTime());
    $this->assertTimezoneIs('Europe/London');
}
```

### Time Travel back to the present
If you are done with time travelling and want to travel back to the present, simply call the `travel` method then chain off and call the `back` method.

```php
public function travel_back_to_present(): void
{
    $this->travelTo('3rd January 2000 15:00:00');

    $this->assertTimeIs('3rd January 2000 15:00:00');

    $this->travel()->back();

    $this->assertEquals(time(), $this->getDrupalTime()->getRequestTime());
}
```
