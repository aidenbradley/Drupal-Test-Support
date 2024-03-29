<?php

namespace Drupal\Tests\test_support\Traits\Installs;

use Drupal\Core\Extension\Dependency;
use Drupal\Core\Serialization\Yaml;
use PHPUnit\Framework\Assert;

trait InstallsModules
{
    /** @var string[] */
    private $modulesToInstall = [];

    /** @param string|string[] $modules */
    public function enableModuleWithDependencies($modules): self
    {
        $this->modulesToInstall = (array) $modules;

        foreach ((array) $modules as $module) {
            $this->recursivelyResolveDependencies($module);
        }

        $this->enableModules($this->modulesToInstall);

        $this->modulesToInstall = [];

        return $this;
    }

    private function recursivelyResolveDependencies(string $module): void
    {
        $dependencies = $this->getModuleDependencies($module);

        $this->modulesToInstall = array_merge($this->modulesToInstall, $dependencies);

        array_walk_recursive($dependencies, function (string $module): void {
            $this->recursivelyResolveDependencies($module);
        });
    }

    /** @return string[] */
    private function getModuleDependencies(string $moduleName): array
    {
        /** @var array<string, array<string>> $infoYaml */
        $infoYaml = $this->getModuleInfo($moduleName);

        if (isset($infoYaml['dependencies']) === false) {
            return [];
        }

        $dependencies = array_map(function (string $dependency): string {
            return Dependency::createFromString($dependency)->getName();
        }, $infoYaml['dependencies']);

        return array_diff($dependencies, $this->modulesToInstall);
    }

    /** @return mixed */
    private function getModuleInfo(string $module)
    {
        $path = null;

        /** @phpstan-ignore-next-line */
        if (version_compare(\Drupal::VERSION, '10.0', '>=')) {
            /** @phpstan-ignore-next-line */
            $path = $this->container->get('extension.path.resolver')->getPath('module', $module);
        } elseif (function_exists('drupal_get_path')) {
            /** @phpstan-ignore-next-line */
            $path = drupal_get_path('module', $module);
        }

        /** @phpstan-ignore-next-line */
        if ($path === null) {
            $this->fail('Could not find path for module: ' . $module);
        }

        $fileLocation = $path . '/' . $module . '.info.yml';

        $yaml = file_get_contents($fileLocation);

        if ($yaml === false) {
            Assert::fail('Could not decode YAML when attempting to install `' . $module . '`');
        }

        return Yaml::decode($yaml);
    }
}
