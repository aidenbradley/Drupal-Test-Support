<?php

namespace Drupal\Tests\test_support\Kernel\Installs;

use Drupal\image\Entity\ImageStyle;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\test_support\Traits\Support\Exceptions\ConfigInstallFailed;
use Drupal\Tests\test_support\Traits\Installs\InstallsExportedConfig;

class InstallsExportedConfigTest extends KernelTestBase
{
    use InstallsExportedConfig {
        configDirectory as InstallsExportedConfigDirectory;
    }

    protected static $modules = [
        'system',
        'node',
        'user',
    ];

    /** @var string */
    private $customConfigDirectory;

    /** @test */
    public function disable_strict_config_schema(): void
    {
        $this->disableStrictConfig();

        $this->assertFalse($this->strictConfigSchema);
    }

    /** @test */
    public function enable_strict_config_schema(): void
    {
        $this->strictConfigSchema = false;

        $this->enableStrictConfig();

        $this->assertTrue($this->strictConfigSchema);
    }

    /** @test */
    public function installs_theme_dependency(): void
    {
        $this->enableModules([
            'image',
        ]);

        $this->setFixtureConfigDirectory('config_dependencies');

        $this->assertEmpty($this->container->get('theme_handler')->listInfo());

        // the "views.view.media.yml" file declares a dependency on the "seven" theme
        $this->installViews('media');

        $this->assertArrayHasKey('seven', $this->container->get('theme_handler')->listInfo());
    }

    /** @test */
    public function installs_config_dependency(): void
    {
        $this->setFixtureConfigDirectory('config_dependencies')->enableModules([
            'image',
        ]);

        $this->installEntitySchema('image_style');

        $this->assertNull(
            $this->container->get('entity_type.manager')->getStorage('image_style')->load('large')
        );

        // the "views.view.media.yml" file declares a dependency on the "image.style.large" config item
        $this->installViews('media');

        $this->assertInstanceOf(
            ImageStyle::class,
            $this->container->get('entity_type.manager')->getStorage('image_style')->load('large')
        );
    }

    /** @test */
    public function installs_module_dependency(): void
    {
        $this->setFixtureConfigDirectory('config_dependencies');

        $this->disableModules([
            'user',
        ]);

        $this->assertModulesDisabled([
            'image',
            'user',
            'media',
        ]);

        // the "views.view.media.yml" file declares a dependency on the "image.style.large" config item
        $this->installViews('media');

        $this->assertModulesEnabled([
            'image',
            'media',
            'user',
        ]);
    }

    /** @test */
    public function throws_exception_for_bad_config(): void
    {
        $this->useVfsConfigDirectory();

        $this->installEntitySchema('node');

        try {
            $this->installExportedConfig('node.type.page');
        } catch (ConfigInstallFailed $exception) {
            $this->assertEquals(ConfigInstallFailed::CONFIGURATION_DOES_NOT_EXIST, $exception->getCode());
            $this->assertEquals('node.type.page', $exception->getFailingConfigFile());
        }
    }

    /** @param string|array $modules */
    private function assertModulesEnabled($modules): self
    {
        foreach ((array) $modules as $module) {
            $this->assertTrue(
                $this->container->get('module_handler')->moduleExists($module)
            );
        }

        return $this;
    }

    /** @param string|array $modules */
    private function assertModulesDisabled($modules): self
    {
        foreach ((array) $modules as $module) {
            $this->assertFalse(
                $this->container->get('module_handler')->moduleExists($module)
            );
        }

        return $this;
    }

    private function setFixtureConfigDirectory(string $directory): self
    {
        $this->setConfigDirectory(__DIR__ . '/__fixtures__/config/sync/' . $directory);

        return $this;
    }
}
