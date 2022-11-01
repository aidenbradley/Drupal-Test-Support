<?php

namespace Drupal\Tests\test_support\Kernel\Installs;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\test_support\Traits\Installs\InstallsTheme;

class InstallsThemeTest extends KernelTestBase
{
    use InstallsTheme;

    /** @test */
    public function installs_theme(): void
    {
        $this->assertEmpty($this->container->get('theme_handler')->listInfo());

        $this->installThemes('seven');

        $this->assertArrayHasKey('seven', $this->container->get('theme_handler')->listInfo());

        $this->installThemes('bartik');

        $this->assertArrayHasKey('bartik', $this->container->get('theme_handler')->listInfo());
    }
}
