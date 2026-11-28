<?php

namespace Drupal\Tests\test_support\Kernel\Installs;

use Drupal\image\Entity\ImageStyle;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\test_support\Traits\Installs\InstallsExportedConfig;
use Drupal\Tests\test_support\Traits\Support\Exceptions\ConfigInstallFailed;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

#[RunTestsInSeparateProcesses]
class InstallsExportedConfigTest extends KernelTestBase
{
    use InstallsExportedConfig {
        configDirectory as InstallsExportedConfigDirectory;
    }

    /** @var string[] */
    protected static $modules = [
        'system',
        'node',
        'user',
    ];

    /** @var string */
    private $customConfigDirectory;

    /** @test */
    #[Test]
    public function disable_strict_config_schema(): void
    {
        $this->disableStrictConfig();

        $this->assertFalse($this->strictConfigSchema);
    }

    /** @test */
    #[Test]
    public function enable_strict_config_schema(): void
    {
        $this->strictConfigSchema = false;

        $this->enableStrictConfig();

        $this->assertTrue($this->strictConfigSchema);
    }

    /** @test */
    #[Test]
    public function installs_theme_dependency(): void
    {
        // Drupal 11.3+'s field type plugin discovery requires image's own
        // parent class's module (FileItem, from the "file" module - image's
        // own FieldType plugin, ImageItem, extends it) to be genuinely
        // enabled, not just loadable - views.view.media.yml's own declared
        // "image" dependency isn't enough on its own. "file" was never
        // explicitly enabled here before Drupal 11 either, it just didn't
        // matter until 11.3's discovery became stricter about it.
        $this->enableModules([
            'file',
            'image',
        ]);

        $this->setFixtureConfigDirectory('config_dependencies');

        $this->assertEmpty($this->container->get('theme_handler')->listInfo());

        // the "views.view.media.yml" file declares a dependency on the "stark" theme
        $this->installViews('media');

        $this->assertArrayHasKey('stark', $this->container->get('theme_handler')->listInfo());
    }

    /** @test */
    #[Test]
    public function installs_config_dependency(): void
    {
        // See the comment on installs_theme_dependency() above - "file"
        // needs enabling too, for the same reason.
        $this->setFixtureConfigDirectory('config_dependencies')->enableModules([
            'file',
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
    #[Test]
    public function installs_module_dependency(): void
    {
        $this->setFixtureConfigDirectory('config_dependencies');

        // See the comment on installs_theme_dependency() above - "file"
        // needs enabling too, for the same reason. It's deliberately left
        // out of the assertions below: this test is specifically about the
        // config's own declared dependencies (image, media, user) getting
        // auto-enabled, and "file" isn't one of those - it's only a
        // transitive dependency of "image" itself, which this module's own
        // dependency resolution doesn't currently chase, same as a real
        // Drupal 9/10/11.0-11.2 site's own install never needed it to.
        $this->enableModules([
            'file',
        ]);

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
    #[Test]
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

    /** @param string|string[] $modules */
    private function assertModulesEnabled($modules): self
    {
        foreach ((array) $modules as $module) {
            $this->assertTrue(
                $this->container->get('module_handler')->moduleExists($module)
            );
        }

        return $this;
    }

    /** @param string|string[] $modules */
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
