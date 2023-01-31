<?php

namespace Drupal\Tests\test_support\Kernel\Support;

use Drupal\Core\DependencyInjection\ContainerBuilder;
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

        if (str_starts_with(\Drupal::VERSION, '10.')) {
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
        $this->settingsLocation = '/__fixtures__/settings/fixture.settings.php';

        if (str_starts_with(\Drupal::VERSION, '10.')) {
            $this->assertEquals(
                $this->container->getParameter('app.root') . '/test/config/directory',
                $this->getConfigurationDirectory()
            );
        } else {
            $this->assertEquals(
                $this->container->get('app.root') . '/test/config/directory',
                $this->getConfigurationDirectory()
            );
        }
    }

    /**
     * @test
     *
     * "auto_discovered" is a setting set at /Kernel/__fixtures__/settings/auto_discover/settings.php
     */
    public function auto_discovers_settings(): void
    {
        $this->markTestSkipped('To be ran locally against a drupal installation that has a valid settings.php');

        $this->assertNull($this->getSettings()->get('auto_discovered'));

        // force InteractsWithSettings to find settings.php again
        // this time using finder to load in other settings
        $this->settings = null;
        $this->autoDiscoverSettings = true;

        $this->assertTrue($this->getSettings()->get('auto_discovered'));
    }
}
