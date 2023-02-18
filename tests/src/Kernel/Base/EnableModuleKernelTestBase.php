<?php

namespace Drupal\Tests\test_support\Kernel\Base;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\test_support\Traits\Installs\InstallsModules;

/**
 * This class will act as a base set of tests to test whether a module will work or not.
 * The idea is to expand this class to provide default coverage for modules where all
 * they need to do is extend this kernel test base and provide their module name
 */
abstract class EnableModuleKernelTestBase extends KernelTestBase
{
    use InstallsModules;

    /** @var array */
    private $entityTypeDefinitionsPreInstall;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityTypeDefinitionsPreInstall = array_keys(
            $this->container->get('entity_type.manager')->getDefinitions()
        );
    }

    abstract public function module(): string;

    /** @test */
    public function install_module_entity_definitions(): void
    {
        $this->enableModuleWithDependencies($this->module());

        $moduleEntityDefinitions = array_diff(
            array_keys($this->container->get('entity_type.manager')->getDefinitions()),
            $this->entityTypeDefinitionsPreInstall,
        );

        foreach($moduleEntityDefinitions as $moduleEntityDefinition) {
            $this->installEntitySchema($moduleEntityDefinition);
        }

        $this->addToAssertionCount(1);
    }
}
