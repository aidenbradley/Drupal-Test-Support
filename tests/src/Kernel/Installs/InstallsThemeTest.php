<?php

namespace Drupal\Tests\test_support\Kernel\Installs;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\test_support\Traits\Installs\InstallsThemes;

class InstallsThemeTest extends KernelTestBase
{
    use InstallsThemes;

    /** @test */
    public function installs_theme(): void
    {
        $this->assertEmpty($this->container->get('theme_handler')->listInfo());

        $this->installThemes('stark');

        $this->assertArrayHasKey('stark', $this->container->get('theme_handler')->listInfo());
    }

    /** @test */
    public function installs_multiple_themes(): void
    {
        $this->assertEmpty($this->container->get('theme_handler')->listInfo());

        $this->installThemes([
            'stark',
            'classy',
        ]);

        $this->assertArrayHasKey('stark', $this->container->get('theme_handler')->listInfo());
        $this->assertArrayHasKey('classy', $this->container->get('theme_handler')->listInfo());
    }
}
