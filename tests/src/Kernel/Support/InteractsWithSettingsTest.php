<?php

namespace Drupal\Tests\test_support\Kernel\Support;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Site\Settings;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\test_support\Traits\Support\InteractsWithSettings;
use Symfony\Component\DependencyInjection\Reference;

class InteractsWithSettingsTest extends KernelTestBase
{
    use InteractsWithSettings;

    protected function setUp(): void
    {
        parent::setUp();

        $container = new ContainerBuilder();

        $container->set('kernel', $this->container->get('kernel'));

        // Drupal 10.3+'s own KernelTestBase::tearDown() unconditionally fetches
        // request_stack to clean up a mock session. Since this test swaps out
        // $this->container for this bare stand-in (to isolate the trait under
        // test from needing the full kernel container), request_stack needs
        // carrying over the same way kernel already is, or tearDown() throws
        // ServiceNotFoundException instead of the SessionNotFoundException it
        // actually knows how to handle.
        $container->set('request_stack', $this->container->get('request_stack'));

        /** @phpstan-ignore-next-line */
        if (version_compare(\Drupal::VERSION, '10.0', '>=')) {
            $container->setParameter('app.root', __DIR__);
        } else {
            $container->set('app.root', new Reference(__DIR__));
        }

        $this->container = $container;
    }

    /**
     * @test
     *
     * "fixture.settings.php" contains a variable not defined in scope when it's loaded in.
     */
    public function supresses_errors_when_requiring_settings(): void
    {
        $this->setSettingsLocation('/__fixtures__/settings/fixture.settings.php');

        // Matches the same check in setUp() above - app.root became a
        // parameter (not a service) starting at Drupal 10, not just for
        // the "10." major specifically. str_starts_with(..., '10.') never
        // matched Drupal 11+, so this always took the wrong (service)
        // branch there.
        if (version_compare(\Drupal::VERSION, '10.0', '>=')) {
            /** @var string $appRoot */
            $appRoot = $this->container->getParameter('app.root');

            $expectedConfigurationDirectory = $appRoot . '/test/config/directory';

            $this->assertEquals($expectedConfigurationDirectory, $this->getConfigurationDirectory());
        } else {
            /** @phpstan-ignore-next-line */
            $expectedConfigurationDirectory = $this->container->get('app.root') . '/test/config/directory';

            $this->assertEquals($expectedConfigurationDirectory, $this->getConfigurationDirectory());
        }
    }

    /**
     * @test
     *
     * "auto_discovered" is a setting set at /Kernel/__fixtures__/settings/auto_discover/settings.php
     */
    public function auto_discovers_settings(): void
    {
        $this->assertNull($this->getSettings()->get('auto_discovered'));

        // force InteractsWithSettings to find settings.php again
        // this time using finder to load in other settings
        $this->settings = null;
        $this->autoDiscoverSettings = true;

        $this->assertTrue($this->getSettings()->get('auto_discovered'));
    }

    /** @test */
    public function set_site(): void
    {
        $this->assertEquals('default', $this->site);

        $this->setSite('my_custom_site');

        $this->assertEquals('my_custom_site', $this->site);
    }

    /** @test */
    public function setting_site_refreshes_settings(): void
    {
        $this->setSettingsLocation('/__fixtures__/settings/fixture.settings.php');

        $this->getSettings();

        $this->assertInstanceOf(Settings::class, $this->settings);

        $this->settings = null;

        $this->setSite('default');

        $this->assertInstanceOf(Settings::class, $this->settings);
    }

    /** @test */
    public function setting_site_updates_settings(): void
    {
        $fixtureSiteDirectory = __DIR__ . '/__fixtures__';

        /** @phpstan-ignore-next-line */
        if (version_compare(\Drupal::VERSION, '10.0', '>=')) {
            $this->container->setParameter('app.root', $fixtureSiteDirectory);
        } else {
            $this->container->set('app.root', new Reference($fixtureSiteDirectory));
        }

        //

        $this->setSite('dummy_site_one');
        $this->assertEquals('/dummy_site_one_config', $this->getSettings()->get('config_sync_directory'));

        $this->setSite('dummy_site_two');
        $this->assertEquals('/dummy_site_two_config', $this->getSettings()->get('config_sync_directory'));
    }

    /** @test */
    public function setting_settings_location_refreshes_settings(): void
    {
        /** @phpstan-ignore-next-line */
        if (version_compare(\Drupal::VERSION, '10.0', '>=')) {
            $this->container->setParameter('app.root', __DIR__);
        } else {
            $this->container->set('app.root', new Reference(__DIR__));
        }

        $this->assertEmpty(
            $this->getSettings()->get('config_sync_directory')
        );

        $this->setSettingsLocation('/__fixtures__/sites/dummy_site_one/settings.php');
        $this->assertEquals('/dummy_site_one_config', $this->getSettings()->get('config_sync_directory'));

        $this->setSettingsLocation('/__fixtures__/sites/dummy_site_two/settings.php');
        $this->assertEquals('/dummy_site_two_config', $this->getSettings()->get('config_sync_directory'));
    }
}
