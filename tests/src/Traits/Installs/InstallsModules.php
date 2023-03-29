<?php

namespace Drupal\Tests\test_support\Traits\Installs;

use Drupal\Core\Serialization\Yaml;

trait InstallsModules
{
    /** @var array */
    private $modulesToInstall = [];

    public function enableModuleWithDependencies($modules): self
    {
        $this->modulesToInstall = collect($modules);

        foreach ((array) $modules as $module) {
            $dependencies = $this->getModuleDependencies($module);

            do {
                foreach($dependencies as $dependency) {
                    $this->modulesToInstall->add($dependency);

                    $dependencies = $this->getModuleDependencies($dependency);

                    $this->modulesToInstall->merge($dependencies);
                }
            } while ($dependencies !== []);
        }

        $this->enableModules($this->modulesToInstall->toArray());

        $this->modulesToInstall = [];

        return $this;
    }

    private function getModuleDependencies(string $moduleName): array
    {
        $infoYaml = $this->getModuleInfo($moduleName);
//        dump($moduleName, $infoYaml);
        if (isset($infoYaml['dependencies']) === false) {
            return [];
        }

        return collect($infoYaml['dependencies'])->map(function(string $dependency): string {
            return $this->handlePrefixes($dependency);
        })->diff($this->modulesToInstall)->toArray();
    }

    /** @return mixed */
    private function getModuleInfo(string $module)
    {
        if (str_starts_with(\Drupal::VERSION, '10.')) {
            $pathResolver = $this->container->get('extension.path.resolver');

            $fileLocation = $pathResolver->getPath('module', $module);
        } else {
            $fileLocation = drupal_get_path('module', $module);
        }

        return Yaml::decode(
            file_get_contents($fileLocation . '/' . $module . '.info.yml')
        );
    }

    private function handlePrefixes(string $moduleName): string
    {
        if (str_contains($moduleName, ':') === false) {
            return $moduleName;
        }

        return collect(explode(':', $moduleName))->last();
    }
}
