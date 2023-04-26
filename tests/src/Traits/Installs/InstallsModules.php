<?php

namespace Drupal\Tests\test_support\Traits\Installs;

use Drupal\Core\Serialization\Yaml;
use PHPUnit\Framework\Assert;

trait InstallsModules
{
    /** @var array */
    private $modulesToInstall = [];

    /** @param string|array $modules */
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

    private function getModuleDependencies(string $moduleName): array
    {
        $infoYaml = $this->getModuleInfo($moduleName);

        if (isset($infoYaml['dependencies']) === false) {
            return [];
        }

        $dependencies = array_map(function (string $dependency): string {
            return $this->handlePrefixes($dependency);
        }, $infoYaml['dependencies']);

        return array_diff($dependencies, $this->modulesToInstall);
    }

    /** @return mixed */
    private function getModuleInfo(string $module)
    {
        $path = null;

        if (str_starts_with(\Drupal::VERSION, '10.')) {
            /** @phpstan-ignore-next-line */
            $path = $this->container->get('extension.path.resolver')->getPath('module', $module);
        }

        if (str_starts_with(\Drupal::VERSION, '9.')) {
            $path = drupal_get_path('module', $module);
        }

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

    private function handlePrefixes(string $moduleName): string
    {
        if (str_contains($moduleName, ':') === false) {
            return $moduleName;
        }

        return collect(explode(':', $moduleName))->last();
    }
}
