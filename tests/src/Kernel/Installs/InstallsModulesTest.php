<?php

namespace Drupal\Tests\test_support\Kernel\Installs;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\test_support\Traits\Installs\InstallsModules;

class InstallsModulesTest extends KernelTestBase
{
    use InstallsModules;

    /**
     * @test
     *
     * Test covering DX improvement whereby the given string is casted to an array when calling $this->enableModules()
     */
    public function enables_modules(): void
    {
        $this->assertModulesDisabled('text')
            ->enableModules('text')
            ->assertModulesEnabled('text');

        $moduleList = [
            'node',
            'media',
        ];
        $this->assertModulesDisabled($moduleList)
            ->enableModules($moduleList)
            ->assertModulesEnabled($moduleList);
    }

    /** @test */
    public function installs_dependencies(): void
    {
        $expectedDependencies = [
            'system',
            'link',
            'text',
            'file',
            'image',
        ];

        $this->assertModulesDisabled($expectedDependencies)
            ->installModuleWithDependencies('test_support_dependencies')
            ->assertModulesEnabled($expectedDependencies);
    }

    private function assertModulesEnabled($modules): self
    {
        foreach ((array) $modules as $module) {
            $this->assertTrue(
                $this->container->get('module_handler')->moduleExists($module)
            );
        }

        return $this;
    }

    private function assertModulesDisabled($modules): self
    {
        foreach ((array) $modules as $module) {
            $this->assertFalse(
                $this->container->get('module_handler')->moduleExists($module)
            );
        }

        return $this;
    }
}
