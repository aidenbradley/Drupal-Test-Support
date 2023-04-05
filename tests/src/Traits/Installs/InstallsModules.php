<?php

namespace Drupal\Tests\test_support\Traits\Installs;

use Drupal\Core\Serialization\Yaml;

trait InstallsModules
{
    /** @var array */
    private $modulesToInstall = [];

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

        array_walk_recursive($dependencies, function(string $module): void {
            $this->recursivelyResolveDependencies($module);
        });
    }

    private function getModuleDependencies(string $moduleName): array
    {
        $infoYaml = $this->getModuleInfo($moduleName);

        if (isset($infoYaml['dependencies']) === false) {
            return [];
        }

        $dependencies = array_map(function(string $dependency): string {
            return $this->handlePrefixes($dependency);
        }, $infoYaml['dependencies']);

        return array_diff($dependencies, $this->modulesToInstall);
    }

    /** @return mixed */
    private function getModuleInfo(string $module)
    {
        if ($this->container->has('extension.path.resolver')) {
            $path = $this->container->get('extension.path.resolver')->getPath('module', $module);
        } else {
            $path = drupal_get_path('module', $module);
        }

        $fileLocation = $path . '/' . $module . '.info.yml';

        return Yaml::decode(file_get_contents($fileLocation));
    }

    private function handlePrefixes(string $moduleName): string
    {
        if (str_contains($moduleName, ':') === false) {
            return $moduleName;
        }

        return collect(explode(':', $moduleName))->last();
    }
}
