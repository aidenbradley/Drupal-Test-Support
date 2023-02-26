<?php

namespace Drupal\Tests\test_support\Traits\Installs;

use Drupal\Core\Serialization\Yaml;

trait InstallsModules
{
    public function enableModuleWithDependencies($modules): self
    {
        foreach ((array) $modules as $module) {
            $infoYaml = $this->getModuleInfoFileContents($module);

            if (isset($infoYaml['dependencies']) === false) {
                $this->enableModules((array) $module);

                continue;
            }

            $cleanedDependencies = array_map(function ($dependency) {
                return str_replace('drupal:', '', $dependency);
            }, $infoYaml['dependencies']);

            $this->enableModules(array_merge((array) $module, $cleanedDependencies));
        }

        return $this;
    }

    private function getModuleInfoFileContents(string $module): array
    {
        if ($this->container->has('extension.path.resolver')) {
            $path = $this->container->get('extension.path.resolver')->getPath('module', $module);
        } else {
            $path = drupal_get_path('module', $module);
        }

        $fileLocation = $path . '/' . $module . '.info.yml';

        return Yaml::decode(file_get_contents($fileLocation));
    }
}
