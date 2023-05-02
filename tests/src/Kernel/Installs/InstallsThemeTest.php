<?php

namespace Drupal\Tests\test_support\Kernel\Installs;

use Drupal\KernelTests\KernelTestBase;
use AidenBradley\DrupalTestSupport\Installs\InstallsTheme;

class InstallsThemeTest extends KernelTestBase
{
    use InstallsTheme;

    /** @test */
    public function installs_theme(): void
    {
        $this->assertEmpty($this->container->get('theme_handler')->listInfo());

        $this->installThemes('stark');

        $this->assertArrayHasKey('stark', $this->container->get('theme_handler')->listInfo());
    }
}
