<?php

namespace Drupal\Tests\test_support\Kernel\Installs;

use Drupal\KernelTests\KernelTestBase;
use AidenBradley\DrupalTestSupport\Installs\InstallsModules;

class InstallsModulesTest extends KernelTestBase
{
    use InstallsModules;

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
            ->enableModuleWithDependencies('test_support_dependencies')
            ->assertModulesEnabled($expectedDependencies);
    }

    /** @test */
    public function handles_nested_mutual_dependencies(): void
    {
        $expectedDependencies = [
            'system',
            'link',
            'text',
            'file',
            'image',
            'test_support_dependencies',
            'test_support_mutual_dependency_one',
            'test_support_mutual_dependency_two',
            'field',
            'filter',
        ];

        $this->assertModulesDisabled($expectedDependencies);

        $this->enableModuleWithDependencies('test_support_mutual_dependency_one');

        $this->assertModulesEnabled($expectedDependencies);
    }

    /** @param string|string[] $modules */
    private function assertModulesEnabled($modules): self
    {
        foreach ((array) $modules as $module) {
            $this->assertTrue(
                $this->container->get('module_handler')->moduleExists($module),
                $module . ' is not enabled'
            );
        }

        return $this;
    }

    /** @param string|string[] $modules */
    private function assertModulesDisabled($modules): self
    {
        foreach ((array) $modules as $module) {
            $this->assertFalse(
                $this->container->get('module_handler')->moduleExists($module),
                $module . ' is not disabled'
            );
        }

        return $this;
    }
}
