<?php

namespace Drupal\Tests\drupal_test_support\Kernel\Installs;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\drupal_test_support\Traits\Installs\InstallsModules;

class InstallsModulesTest extends KernelTestBase
{
    use InstallsModules;

    /** @test */
    public function installs_dependencies(): void
    {
        $moduleHandler = $this->container->get('module_handler');

        $expectedDependencies = [
            'system',
            'link',
            'text',
            'file',
            'image',
        ];

        foreach ($expectedDependencies as $dependency) {
            $this->assertFalse($moduleHandler->moduleExists($dependency));
        }

        $this->installModuleWithDependencies('drupal_test_support_dependencies');

        foreach ($expectedDependencies as $dependency) {
            $this->assertTrue($moduleHandler->moduleExists($dependency));
        }
    }
}
