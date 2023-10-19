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
- [Time Travel to a date and time in a timezone](#time-travel-to-a-date-and-time-in-a-timezone)
- [Time Travel to a timezone](#time-travel-to-a-timezone)
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

To do this, pass a closure argument to the `toTimezone` method. Anything inside of this closure will be executed in that timezone. Once completed, you will be returned to the present!

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

## Interacting with entities
There is a trait called [InteractsWithEntities](.././tests/src/Traits/Support/InteractsWithEntities.php) that contains an API to improve the developer experience of interacting with entities.

The API is very minor and only serves the purpose of making the tests inside this module more readable, but the trait is available to you regardless.

### Creating an entity
To create an entity, call the `createEntity` method.

It accepts two arguments. The first argument is the entity type you want to create and the second argument is simply an array of values you want to store against the entity to be created.

Under the hood, this will create the entitiy and save it to the database for you.

```php
public function create_entity(): void
{
    $node = $this->createEntity('node', [
        'title' => 'Example node',
        'type' => 'page',
    ]);
}
```
### Updating an entity
To update an entity, call the `updateEntity` method.

It accepts two arguments. The first argument is the entity to update and the second argument is simply an array of values you want to update.

```php
public function update_entity(): void
{
    $node = $this->createEntity('node', [
        'title' => 'Example node',
        'type' => 'page',
    ]);

    $this->updateEntity($node, [
        'title' => 'Updated Example Title',
    ]);
}
```

### Refreshing an entity
Refreshing an entity is quite useful. Often times, you may be working with an entity that's updated somewhere else in the system as part of your business logic. Loading the entity again is fine, but feels cumbersome. For this reason, the API provides a method to retreive the updated entity. We call this "refreshing".

To refresh an entity, call the `refreshEntity` method.

Under the hood, this will reload the entity for you and assign it back to the variable you give the `refreshEntity` method.

```php
public function refresh_entity(): void
{
    $node = $this->createEntity('node', [
        'title' => 'Example Title',
        'type' => 'page',
    ]);

    $this->assertEquals('Example Title', $node->get('title')->getString());

    // Somewhere in your business logic, the title of the node is updated
    $this->businessLogic();

    // Get all updated values for this entity, but assign
    // it back to the variable we have already defined
    $this->refreshEntity($node);

    $this->assertEquals('Example Title Updated', $node->get('title')->getString());
}
```

## Interacting with Languages
There is a trait called [InteractsWithLanguages](.././tests/src/Traits/Support/InteractsWithLanguages.php) that contains an API to improve the developer experience of installing and setting the current language when running tests.

Under the hood, the trait will enable and install the config from the `language` module and install the `configurable_language` entity schema.

### Installing a language
When performing a test, you may want to install a language so that is available to the rest of your Drupal application. This could be if you are testing creating or updating translations.

To install a language, you must export the language configuration into your configuration sync directory.

To install a language, call the `installLanguage` method with an argument of the language code.

```php
public function install_language(): void
{
    // Install the German language
    $this->installLanguage('de');

    // Install the French language
    $this->installLanguage('fr');
}
```

### Installing multiple languages
To install multiple languages at once, call the `installLanguage` method and pass an array of langcodes.

```php
public function install_multiple_languages(): void
{
    // Install the German and French languages
    $this->installLanguage([
        'de',
        'fr',
    ]);
}
```

### Setting the current language
During your test run, you may want to set the current language. This means that any code that's executed during your test is executed in the context of that language.

For example, you can set the current language before creating an entity. Once the entity is created, the langcode of that entity will match the current language you have set.

There are two ways in which you can set the current language

#### Set the current language using langcode
To set the current language, call the `setCurrentLanguage` method and pass a langcode.

Under the hood, it will install the language if it hasn't been installed already.

```php
public function set_current_language(): void
{
    $englishNode = Node::create([
        'title' => 'EN Node',
        'type' => 'page',
    ]);
    $englishNode->save();
    $this->assertEquals('en', $englishNode->language()->getId());

    $this->setCurrentLanguage('fr');

    $frenchNode = Node::create([
        'title' => 'FR Node',
        'type' => 'page',
    ]);
    $frenchNode->save();
    $this->assertEquals('fr', $frenchNode->language()->getId());
}
```

#### Set the current language using Language object
To set the current language, call the `setCurrentLanguage` method and pass a Language object.

Under the hood, it will install the language if it hasn't been installed already.

```php
public function set_current_language_using_language_class(): void
{
    $this->installLanguage('de');

    $this->assertEquals('en', $this->languageManager()->getCurrentLanguage()->getId());

    $germanLanguage = $this->languageManager()->getLanguage('de');

    $this->setCurrentLanguage($germanLanguage);

    $this->assertEquals('de', $this->languageManager()->getCurrentLanguage()->getId());
}
```

### Setting the current language with a prefix
When setting the current language, you may also set the prefix. This is handy if you want to test against generated URL's, for example.

To set the prefix, call the `setCurrentLanguage` method and pass a second argument of the language prefix.

```php
public function set_current_language_with_prefix(): void
{
    $this->setCurrentLanguage('fr', 'fr-prefix');

    $frenchNode = $this->nodeStorage()->create([
        'nid' => '3000',
        'title' => 'FR Node',
        'type' => 'page',
    ]);
    $frenchNode->save();

    $url = $frenchNode->toUrl()->toString(true)->getGeneratedUrl();

    $this->assertEquals('/fr-prefix/node/3000', $url);
}
```

## Interacts with Mail
There is a trait called [InteractsWithMail](.././tests/src/Traits/Support/InteractsWithMail.php) that contains an API to improve the developer experience of testing against emails that are sent.

This API provides methods to -
- Get any mail that has been sent
- Assert against any mail that has been sent
- Inspect the contents of the mail that has been sent

When using this API, any mail that's found is returned to you using the [TestMail](.././tests/src/Traits/Support/Mail/TestMail.php) class.

### Helper methods
The [InteractsWithMail](.././tests/src/Traits/Support/InteractsWithMail.php) trait contains helper methods that improve the developer experience of getting certain mail that's been sent.

#### Getting sent mail
To get sent mail, call the `getSentMail` method.

This will return an array of [TestMail](.././tests/src/Traits/Support/Mail/TestMail.php) instances.

```php
public function get_sent_mail(): void
{
    $this->container->get('plugin.manager.mail')->mail(
        'test_support_mail',
        'test_support_mail',
        'hello@example.com',
        'en',
        [],
        null, // no reply
        true // send mail
    );

    // Returns an array containing one instance of TestMail
    $sentMail = $this->getSentMail();
}
```

##### Filtering the results of `getSentMail`
The `getSentMail` accepts a single argument of `closure`. This is used to filter what is returned by the method.

Here is an example of filtering mail to only mail that's sent with a certain subject.

```php
public function get_sent_mail_with_subject(): void
{
    $mailWithWelcomeSubject = $this->getSentMail(function (TestMail $mail): bool {
        return $mail->getSubject() === 'Welcome to Drupal!';
    });
}
```

#### Getting mail sent to an email address
To get mail that has been sent to a certain email address, call the `getMailSentTo` method.

If only one mail item is found, then the method will return a single [TestMail](.././tests/src/Traits/Support/Mail/TestMail.php) instance.

If multiple mail items are found, then the method will return an array of [TestMail](.././tests/src/Traits/Support/Mail/TestMail.php) instances.

```php
public function get_mail_sent_to(): void
{
    $this->container->get('plugin.manager.mail')->mail(
        'test_support_mail',
        'test_support_mail',
        'hello@example.com',
        'en',
        [],
        null, // no reply
        true // send mail
    );

    // Returns an instance of TestMail as only one mail has been sent
    $sentMail = $this->getSentMail();

    $this->container->get('plugin.manager.mail')->mail(
        'test_support_mail',
        'test_support_mail',
        'hello@example.com',
        'en',
        [],
        null, // no reply
        true // send mail
    );

    // Returns an array containing two instances of TestMail as multiple mails have been sent
    $sentMail = $this->getSentMail();
}
```

#### Getting mail sent with a subject
To get sent mail that has a certain subject, call the `getSentMailWithSubject` method.

If only one mail item is found, then the method will return a single [TestMail](.././tests/src/Traits/Support/Mail/TestMail.php) instance.

If multiple mail items are found, then the method will return an array of [TestMail](.././tests/src/Traits/Support/Mail/TestMail.php) instances.

```php
public function get_sent_mail_with_subject(): void
{
    $this->container->get('plugin.manager.mail')->mail(
        'test_support_mail',
        'test_support_mail',
        'hello@example.com',
        'en',
        [],
        null, // no reply
        true // send mail
    );

    $mailWithSubject = $this->getSentMailWithSubject('Welcome to Drupal!');
}
```

#### Clearing sent mail
If you want to clear any mail that's been sent during your test run, then call the `clearMail` method.

This will clear all collected mail.


```php
public function clear_mail(): void
{
    $this->container->get('plugin.manager.mail')->mail(
        'test_support_mail',
        'test_support_mail',
        'hello@example.com',
        'en',
        [],
        null, // no reply
        true // send mail
    );

    $this->assertNotEmpty(
        $this->getSentMail()
    );

    $this->clearMail();

    $this->assertEmpty(
        $this->getSentMail()
    );
}
```

### Assertion helper methods
There are a number of methods provided by [InteractsWithMail](.././tests/src/Traits/Support/InteractsWithMail.php) that allow you to assert against mail sent during your test.

These helper methods exist to improve the readability of your tests.

#### Asserting no mail has been sent
To assert no mail has been sent, call the `assertNoMailSent` method.

```php
public function assert_no_mail_sent(): void
{
    $this->businessLogic();

    $this->assertNoMailSent();
}
```

#### Asserting mail was sent
To assert mail was sent, call the `assertMailSent` method.

This will check to see if any mail was sent at all.

```php
public function assert_mail_sent(): void
{
    $this->sendWelcomeEmails();

    $this->assertMailSent();
}
```

##### Asserting mail was sent with further assertions
The `assertMailSent` method allows you to pass in a closure. This closure is executed after asserting mail has been sent.

This is useful for grouping further assertions!

```php
public function assert_mail_sent_with_closure(): void
{
    $this->container->get('plugin.manager.mail')->mail(
        'test_support_mail',
        'test_support_mail',
        'hello@example.com',
        'en',
        [],
        null, // no reply
        true // send mail
    );

    $this->assertMailSent(function (TestMail $mail): void {
        $mail->assertSentTo('hello@example.com');
    });
}
```

#### Asserting the number of mail sent
To assert the number of mail that's been sent, call the `assertMailSentCount` method.

```php
public function assert_number_of_mail_sent(): void
{
    $this->container->get('plugin.manager.mail')->mail(
        'test_support_mail',
        'test_support_mail',
        'hello@example.com',
        'en',
        [],
        null, // no reply
        true // send mail
    );

    $this->assertMailSentCount(1);

    $this->container->get('plugin.manager.mail')->mail(
        'test_support_mail',
        'test_support_mail',
        'hello@example.com',
        'en',
        [],
        null, // no reply
        true // send mail
    );

    $this->assertMailSentCount(2);
}
```

#### Asserting mail sent from a module
To assert that mail was sent from a particular module, call the `assertMailSentFromModule` method.

```php
public function assert_mail_sent_from_module(): void
{
    $this->container->get('plugin.manager.mail')->mail(
        'test_support_mail',
        'test_support_mail',
        'hello@example.com',
        'en',
        [],
        null, // no reply
        true // send mail
    );

    $this->assertMailSentFromModule('test_support_mail');
}
```

##### Asserting mail sent from a module with further assertions
The `assertMailSentFromModule` method allows you to pass in a closure. This closure is executed after asserting mail has been sent.

This is useful for grouping further assertions!

```php
public function assert_mail_sent_from_module(): void
{
    $this->container->get('plugin.manager.mail')->mail(
        'test_support_mail',
        'test_support_mail',
        'hello@example.com',
        'en',
        [],
        null, // no reply
        true // send mail
    );

    $this->assertMailSentFromModule('test_support_mail', function (TestMail $mail): void {
        $mail->assertSentTo('hello@example.com');
        $mail->assertSubject('Hello');
    });
}
```

#### Assering mail was not sent by module
To assert that no mail was sent by a particular module, call the `assertNoMailSentFromModule`.

```php
public function assert_no_mail_sent_from_module(): void
{
    $this->businessLogic();

    $this->assertNoMailSentFromModule('my_custom_module');
}
```

#### Asserting mail was sent to an email address
To assert that mail was sent to a certain email address, call the `assertMailSentTo` method.

```php
public function assert_mail_sent_to(): void
{
    $this->container->get('plugin.manager.mail')->mail(
        'test_support_mail',
        'test_support_mail',
        'hello@example.com',
        'en',
        [],
        null, // no reply
        true // send mail
    );

    $this->assertMailSentTo('hello@example.com');
}
```

##### Asserting mail was sent to an email address with further assertions
The `assertMailSentTo` method allows you to pass in a closure. This closure is executed after asserting mail has been sent.

This is useful for grouping further assertions!
```php
public function assert_mail_sent_to(): void
{
    $this->container->get('plugin.manager.mail')->mail(
        'test_support_mail',
        'test_support_mail',
        'hello@example.com',
        'en',
        [],
        null, // no reply
        true // send mail
    );

    $this->assertMailSentTo('hello@example.com', function (TestMail $mail) {
        $mail->assertSubject('Hello');
    });
}
```

#### Asserting mail no was sent to an email address
To assert that no mail was sent to an email address, call the `assertNoMailSentTo` method.

```php
public function assert_no_mail_sent_to_address(): void
{
    $this->container->get('plugin.manager.mail')->mail(
        'test_support_mail',
        'test_support_mail',
        'hello@example.com',
        'en',
        [],
        null, // no reply
        true // send mail
    );

    $this->assertNoMailSentTo('test@example.com');
}
```

#### Asserting mail was sent with subject
To assert that mail was sent with a certain subject, call the `assertMailSentWithSubject` method.

```php
public function assert_mail_sent_with_subject(); void
{
    $this->assertMailSentWithSubject('Welcome to Drupal!');
}
```

##### Asserting mail was sent with subject with further assertions
The `assertMailSentWithSubject` method allows you to pass in a closure. This closure is executed after asserting mail has been sent.

This is useful for grouping further assertions!

```php
public function assert_mail_sent_with_subject(): void
{
    $this->container->get('plugin.manager.mail')->mail(
        'test_support_mail',
        'test_support_mail',
        'hello@example.com',
        'en',
        [],
        null, // no reply
        true // send mail
    );

    $this->assertMailSentWithSubject('User Registration', function (TestMail $mail) {
        $mail->assertSentTo('hello@example.com');
        $mail->assertSubject('Welcome to Drupal!');
    });
}
```

### Asserting no mail is sent with subject
To assert that no mail was sent with a certain subject, call the `assertNoMailSentWithSubject` method.

```php
public function assert_no_mail_sent_with_subject(): void
{
    $this->registerNewUser();

    $this->assertMailSentWithSubject('Welcome to Drupal!');

    $this->assertNoMailSentWithSubject('Thanks for updating your account!');
}
```

### TestMail
When retreiving any mail that's been sent, the values are represented using a class called [TestMail](.././tests/src/Traits/Support/Mail/TestMail.php).

This class provides an API to retrieve data and run assertions against data.

#### Getting data helper methods
There are methods on the [TestMail](.././tests/src/Traits/Support/Mail/TestMail.php) that help you retrieve any data that forms part of the data.

Below is a list of methods made available.
##### Get to address
To get the email address the mail was sent to, call the `getTo` method.

```php
public function get_to_address(): void
{
    $this->registerNewUser('hello@example.com');

    $mail = $this->getSentMailTo('hello@example.com');

    $this->assertEquals('hello@example.com', $mail->getToAddress());
}
```

##### Get subject
To get the subject of the mail, call the `getSubject` method.

```php
public function get_subject(): void
{
    $this->registerNewUser('hello@example.com');

    $mail = $this->getSentMailTo('hello@example.com');

    $this->assertEquals('Welcome to Drupal!', $mail->getSubject());
}
```

##### Get body
To get the body of the mail, call the `getBody` method.

```php
public function get_body(): void
{
    $this->registerNewUser('hello@example.com');

    $mail = $this->getSentMailTo('hello@example.com');

    $expectedBody = 'Thanks for joining Drupal!';

    $this->assertEquals($expectedBody, $mail->getBody());
}
```

##### Get module
If you want to get the module that was responsible for sending the mail, call the `getModule` method.

```php
public function get_module(): void
{
    $this->registerNewUser('hello@example.com');

    $mail = $this->getSentMailTo('hello@example.com');

    $this->assertEquals('my_custom_registration_module', $mail->getModule());
}
```

##### Get mail parameters
If you want to get any other parameters that form part of the mail, call the `getParameter` method.

```php
public function get_parameter(): void
{
    $this->registerNewUser('hello@example.com');

    $mail = $this->getMailSentTo('hello@example.com');

    $parameter = $mail->getParameter('my_custom_parameter');
}
```

##### Get all values
If you simply just want to get all the values of the mail, call the `toArray` method. This will return an array of all the values.

```php
public function get_all_mail_values(): void
{
    $this->registerNewUser('hello@example.com');

    $mail = $this->getMailSentTo('hello@example.com');

    $mailValues = $mail->toArray();
}
```

#### Assertion helper methods
There are methods on the [TestMail](.././tests/src/Traits/Support/Mail/TestMail.php) that help you assert against any data that forms part of the data.

Below is a list of methods made available.

##### Assert sent to address
To assert that the mail item was sent to a certain email address, call the `assertSentTo` method.

```php
public function assert_sent_to(): void
{
    $this->registerNewUser('hello@example.com');

    $mail = $this->getMailSentTo('hello@example.com');

    $mail->assertSentTo('hello@example.com');
}
```

##### Assert subject
To assert the subject of the mail item, call the `assertSubject` method.

```php
public function assert_subject(): void
{
    $this->registerNewUser('hello@example.com');

    $mail = $this->getMailSentTo('hello@example.com');

    $mail->assertSubject('Welcome to Drupal!');
}
```

##### Asserting body contents
To assert against the contents of the mail body, call the `assertBody` method.

```php
public function assert_body_contents(): void
{
    $this->registerNewUser('hello@example.com');

    $mail = $this->getMailSentTo('hello@example.com');

    $mail->assertBody('Welcome to Drupal! Visit the link below to get set up');
}
```

##### Asserting mail sent from module
To assert that the mail was sent from a particular module, call the `assertSentFromModule` method.

```php
public function assert_sent_from_module(): void
{
    $this->registerNewUser('hello@example.com');

    $mail = $this->getMailSentTo('hello@example.com');

    $mail->assertSentFromModule('my_custom_registration_module');
}
```

##### Asserting a parameter of mail
If you want to assert the value of a parameter used in the mail, call the `assertParameter` method.

```php
public function assert_parameter(): void
{
    $this->registerNewUser('hello@example.com');

    $mail = $this->getMailSentTo('hello@example.com');

    $mail->assertParameter('my_custom_parameter', 'Custom Parameter');
}
```

#### Getting all mail values
If you want to get all mail values, call the `toArray` method.

```php
public function get_all_mail_values(): void
{
    $mailValues = $mail->toArray();
}
```

## Interacts With Queues
There is a trait called [InteractsWithQueues](.././tests/src/Traits/Support/InteractsWithQueues.php) that contains an API to improve the developer experience of testing functionality that uses Drupal queues.

### Getting a queue
To get a Drupal queue, call the `getQueue` method.

```php
public function get_queue(): void
{
    $queue = $this->getQueue('my_custom_queue');
}
```

### Getting a reliable queue
If you specifically need the "reliable" queue, then call the `getReliableQueue` method.

According to Drupal documentation, you typically want a reliable queue if the ordering of items and guaranteeing every item executes at least once is important.

```php
public function get_reliable_queue(): void
{
    $reliableQueue = $this->getReliableQueue('my_custom_queue');
}
```

### Adding to a queue
If you want to add an item to a queue, call the `addToQueue` method.

The method expects the first argument to be the name of the queue and the second argument to be the data that's is stored in the queue for processing later.

```php
public function add_to_queue(): void
{
    $nodeIdsToUnpublish = [
        1,
        2,
        3,
    ];

    $this->addToQueue('unpublish_node_queue', $nodeIdsToUnpublish);
}
```

### Processing a queue
If you want to process all the data that's currently in a particular queue, call the `processQueue` method.

```php
public function process_queue(): void
{
    $nodeIdsToUnpublish = [
        1,
        2,
        3,
    ];
    $this->addToQueue('unpublish_node_queue', $nodeIdsToUnpublish);

    $this->processQueue('unpublish_node_queue');
}
```

### Clearing a queue
To clear a queue of all its pending items, call the `clearQueue` method.

```php
public function clear_queue(): void
{
    $this->clearQueue('unpublish_node_queue');
}
```

### Getting the queue count
To get a count of the total number of items in a particular queue, call the `getQueueCount` method.

```php
public function get_queue_count(): void
{
    $nodeIdsToUnpublish = [
        1,
        2,
        3,
    ];
    $this->addToQueue('unpublish_node_queue', $nodeIdsToUnpublish);
    $this->assertCount(1, $this->getQueueCount('unpublish_node_queue'));

    $nodeIdsToUnpublish = [
        4,
        5,
        6,
    ];
    $this->addToQueue('unpublish_node_queue', $nodeIdsToUnpublish);
    $this->assertCount(2, $this->getQueueCount('unpublish_node_queue'));
}
```

## Interacts With Settings
There is a trait called [InteractsWithSettings](.././tests/src/Traits/Support/InteractsWithSettings.php).

This trait exists to support other traits, such as [InstallsExportedConfig](.././tests/src/Traits/Support/InstallsExportedConfig.php), but can be used on its own if you have a need to retrieve settings.

### Getting setings
If you want to retrieve the settings that are picked up and used during your test run, call the `getSettings` method.

The settings that are returned will be the settings found inside your sites `settings.php` file. This is dependent on the site that's been set during your test run.

The default site that's set at the start of a test is `default`, meaning it will look inside of `/sites/default/settings.php` to retrieve your `settings.php` file.

```php
public function get_settings(): void
{
    $settings = $this->getSettings();
}
```

### Setting your site
This trait also provides a method to set your site. This is useful if you are testing multi-site project. The default site is set to `default`

When setting the site, the trait will reload the settings based on the site you have set. It does this by looking inside of that sites `settings.php` file.

To set the site, call the `setSite` method.

```php
public function set_site(): void
{
    $this->setSite('first_multisite');
    // this will look inside /sites/first_multisite/settings.php
    $firstMultisiteSettings = $this->getSettings();

    $this->setSite('second_multisite');
    // this will look inside /sites/second_multisite/settings.php
    $secondMultisiteSettings = $this->getSettings();
}
```

### Setting the settings.php location
If, for some reason, you need to manually tell the test the location to the `settings.php` file is, you can call the `setSettingsLocation` method.

This may be useful if you want to run your test against a `settings.php` fixture.

```php
public function set_settings_location(): void
{
    $this->setSettingsLocation('/test_module/__fixtures__/settings.php');

    $this->yourBusinessLogicUsingSettingsLocationAbove();
}
```


### Getting the settings location
If you want to check which `settings.php` file is being used during your test run, call the `getSettingsLocation` method.

This will return the full path to the `settings.php` file that's being used during the test run.

This method also takes into account if you have used the `setSettingsLocation` method.

```php
public function get_settings_location(): void
{
    $settingsLocation = $this->getSettingsLocation();
}
```

## Interacting with update hooks
There is a trait called [InteractsWithUpdateHooks](.././tests/src/Traits/Support/InteractsWithUpdateHooks.php) that contains an API to improve the developer experience of testing update hooks.

The trait allows you to run the following -
- Update hooks
- Post update hooks
- Deploy hooks (drush).

The trait will include the necessary file, ensuring that the hook can be called, as well as enable the module that defines the hook if it's not already defined.

The trait will also handle batching your update hook uses it! This is handled under the hood, so you don't need to call any extra methods to test batching.

### Running an update hook
To run an update hook, call the `runUpdateHook` method. It accepts one argument, which is the name of the update hook function as a string.

Update hooks are defined by modules and live inside your modules' `.install` file.

```php
public function run_update_hook(): void
{
    $this->runUpdateHook('my_module_update_hook_9001');
}
```

### Running post update hooks
To run post update hooks, call the `runPostUpdateHook` method. It accepts one argument, which is the name of the post update function as a string.

Post update hooks are defined by modules and live inside your modules' `.post_update.php` file.

```php
public function run_post_update_hook(): void
{
    $this->runPostUpdateHook('my_module_post_update_hook');
}
```

### Running deploy hooks
To run a deploy hook, call the `runDeployHook` method. It accepts one argument, which is the name of the deploy hook function as a string.

Deploy hooks are something introduced by Drush 10.3 and live inside your modules' `.deploy.php` file.

```php
public function run_deploy_hook(): void
{
    $this->runDeployHook('my_module_deploy_hook');
}
```


## Without events
There is a trait called [WithoutEvents](.././tests/src/Traits/Support/WithoutEvents.php) that contains an API to improve the developer experience of testing events.

### Preventing events from dispatching
To prevent all events from being dispatched, call the `withoutEvents` method.

Calling this method will prevent all events from dispatching, meaning that event subscribers will not trigger as the event is not being dispatched.

Calling `withoutEvents` is required before any of the below methods may be called. This is because when `withoutEvents` is called, a fake event dispatcher is used to collect all events that are dispatched, making assertions possible.

```php
public function prevent_events_from_dispatching(): void
{
    $this->withoutEvents();
}
```

### Expecting events
You can also expect an event. This is like a pre-assertion, useful when you expect a particular event to be fired under some specific business logic.

You have two options when expecting an event, either by the event name or the event class string.

Under the hood, calling `expectsEvents` will call `withoutEvents` for you.

```php
public function expecting_events_by_event_name(): void
{
    $this->expectsEvents('my_test_event');

    $event = new \Drupal\locale\LocaleEvent\LocaleEvent([
        'en',
        'de',
    ]);

    $this->container->get('event_dispatcher')->dispatch($event, 'my_test_event');
}
```

```php
public function expecting_events_by_event_class(): void
{
    $this->expectsEvents(\Drupal\locale\LocaleEvent::class);

    $event = new \Drupal\locale\LocaleEvent\LocaleEvent([
        'en',
        'de',
    ]);

    $this->container->get('event_dispatcher')->dispatch($event, 'my_test_event');
}
```
### Not expecting events
You can tell a test to not expect a certain event. This acts as a per-assertion, useful for when you want to assert an event is not triggered under certain business logic.

You have two options when not expecting an event, either by the event name or the event class string.

Under the hood, calling `doesntExpectEvents` calls `withoutEvents` for you.
```php
public function not_expecting_events_by_event_name(): void
{
    $this->doesntExpectEvents('some_other_event');

    $event = new \Drupal\locale\LocaleEvent\LocaleEvent([
        'en',
        'de',
    ]);

    $this->container->get('event_dispatcher')->dispatch($event, 'my_test_event');
}
```

```php
public function not_expecting_events_by_event_class(): void
{
    $this->doesntExpectEvents(\Drupal\my_module\SomeEvent::class);

    $event = new \Drupal\locale\LocaleEvent\LocaleEvent([
        'en',
        'de',
    ]);

    $this->container->get('event_dispatcher')->dispatch($event, 'my_test_event');
}
```

### Asserting events are dispatched
You can assert that certain events have been dispatched during your test.

To do this, you must first call the `withoutEvents` method to prepare your test.

Next, call the `assertDispatched` method. You can assert that an event was dispatched either by its event name or by the event class.

```php
public function assert_dispatched_class_string(): void
{
    $this->withoutEvents();

    $langcodes = [
        'en',
        'de',
        'fr',
    ];

    $event = new LocaleEvent($langcodes);

    $this->container->get('event_dispatcher')->dispatch($event, 'test_event');

    $this->assertDispatched('test_event');
}
```

```php
public function assert_dispatched_class_string(): void
{
    $this->withoutEvents();

    $langcodes = [
        'en',
        'de',
        'fr',
    ];

    $event = new LocaleEvent($langcodes);

    $this->container->get('event_dispatcher')->dispatch($event, 'test_event');

    $this->assertDispatched(\Drupal\locale\LocaleEvent\LocaleEvent::class);
}
```

#### Asserting events are dispatched with further assertions
The `assertDispatched` method also allows you to pass a callable to make further assertions.

The way this works is once the `assertDispatched` has found the event(s) you are expecting to dispatch, it will pass each event into the callable. This allows you to make further assertions on the events that have been dispatched.

```php
public function assert_dispatched_with_further_assertions(): void
{
    $this->withoutEvents();

    $langcodes = [
        'en',
        'de',
        'fr',
    ];

    $event = new LocaleEvent($langcodes);

    $this->container->get('event_dispatcher')->dispatch($event, 'test_event');

    $this->assertDispatched('test_event', function (LocaleEvent $firedEvent) use ($langcodes) {
        return $firedEvent->getLangcodes() === $langcodes;
    });
}
```
### Asserting events are not dispatched
You can assert that certain events are not dispatched in your tests.

To do this, you must first call the `withoutEvents` method to prepare your test.

Next, call the `assertNotDispatched` method. You can assert that an event was not dispatched either by its event name or by the event class.

```php
public function assert_event_not_dispatched_by_event_name(): void
{
    $this->withoutEvents();

    $eventToDispatch = $this->createEvent();

    $this->container->get('event_dispatcher')->dispatch($eventToDispatch, 'dispatch_event');

    $this->assertNotDispatched('test_event');
}
```

```php
public function assert_event_not_dispatched_by_class_string(): void
{
    $this->withoutEvents();

    $eventToDispatch = $this->createEvent();

    $this->container->get('event_dispatcher')->dispatch($eventToDispatch, 'dispatch_event');

    $this->assertNotDispatched(\Drupal\locale\LocaleEvent\LocaleEvent::class);
}
```

## Without event subscribers
There is a trait called [WithoutEventSubscribers](.././tests/src/Traits/Support/WithoutEventSubscribers.php) that contains an API to improve the developer experience of testing event subscribers.

### Preventing subscribers from listening
In your test, you can prevent all or some event subscribers from acting at all.

To do this, call the `withoutSubscribers` method. This will prevent any subscribers from acting at all when events being dispatched.

```php
public function without_subscribers(): void
{
    $this->withoutSubscribers();
}
```

### Preventing certain subscribers from listening
In your test, you can prevent a certain list of event subscribers from acting when events are dispatched.

To do this, call the `withoutSubscribers` method and pass either one or more event subscribers to prevent them from acting. You can pass either the service ID of the event subscriber or the class string.

#### Preventing a single event subscriber by service ID
```php
public function prevent_single_event_subscriber_by_service_id(): void
{
    $this->enableModules([
        'language',
    ]);

    $this->withoutSubscribers('language.config_subscriber');
}
```

#### Preventing a single event subscriber by class string
```php
public function prevent_single_event_subscriber_by_class_string(): void
{
    $this->enableModules([
        'node',
    ]);

    $this->withoutSubscribers(\Drupal\node\Routing\RouteSubscriber::class);
}
```

#### Preventing multiple event subscribers by service ID
```php
public function prevent_multiple_event_subscriber_by_service_id(): void
{
    $this->enableModules([
        'language',
        'node',
    ]);

    $this->withoutSubscribers([
        'node.route_subscriber',
        'language.config_subscriber',
    ]);
}
```

#### Preventing multiple event subscribers by class string
```php
public function prevent_multiple_event_subscriber_by_class_string(): void
{
    $this->enableModules([
        'language',
        'node',
    ]);

    $this->withoutSubscribers([
        \Drupal\node\Routing\RouteSubscriber::class,
        \Drupal\language\EventSubscriber\ConfigSubscriber::class,
    ]);
}
```

### Asserting an event subscriber is not listening
You can assert in your test that a particular event subscriber is not listening.

To do this, call the `assertNotListening` method. The method allows you to pass an argument of either the service ID of the event subscriber or the class string of the event subscriber.

```php
public function assert_not_listening_service_id(): void
{
    $this->assertNotListening('system.timezone_resolver');
}
```

```php
public function assert_not_listening_class_string(): void
{
    $this->assertNotListening(\Drupal\system\TimeZoneResolver::class);
}
```

### Asserting an event subscriber is not listening to a certain event
You can assert in your test that a particular event subscriber is not listening for a particular event.

To do this, call the `assertNotListening` method and pass a second argument. The second argument will be service ID of the event.

```php
public function assert_not_listening_to_event_by_service_id(): void
{
    $this->assertNotListening('system.timezone_resolver', 'kernel.request');
}
```

```php
public function assert_not_listening_to_event_by_class_string(): void
{
    $this->assertNotListening(\Drupal\system\TimeZoneResolver::class, 'kernel.request');
}
```

### Asserting an event subscriber is listening
You can assert in your test that a particular event subscriber is listening. This is useful to ensure your event subscribers are registered by your module.

To do this, call the `assertListening` method. The method allows you to pass an argument of either the service ID of the event subscriber or the class string of the event subscriber.

```php
public function assert_event_subscriber_listening_by_service_id(): void
{
    $this->assertNotListening('system.timezone_resolver');

    $this->enableModules([
        'system',
    ]);

    $this->assertListening('system.timezone_resolver');
}
```

```php
public function assert_event_subscriber_listening_by_class_string(): void
{
    $this->assertNotListening(TimeZoneResolver::class);

    $this->enableModules([
        'system',
    ]);

    $this->assertListening(TimeZoneResolver::class);
}
```

### Assert event subscriber listening to a certain event
You can assert in your test that a particular event subscriber is listening for a particular event.

To do this, call the `assertListening` method and pass a second argument. The second argument will be service ID of the event.

```php
public function assert_event_subscriber_listening_to_certain_event_by_service_id(): void
{
    $this->assertNotListening('system.timezone_resolver', KernelEvents::REQUEST);

    $this->enableModules([
        'system',
    ]);

    $this->assertListening('system.timezone_resolver', KernelEvents::REQUEST);
}
```

```php
public function assert_event_subscriber_listening_to_certain_event_by_class_string(): void
{
    $this->assertNotListening(TimeZoneResolver::class, KernelEvents::REQUEST);

    $this->enableModules([
        'system',
    ]);

    $this->assertListening(TimeZoneResolver::class, KernelEvents::REQUEST);
}
```
