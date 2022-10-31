<?php

namespace Drupal\Tests\test_support\Traits\Installs;

use Drupal\Core\Serialization\Yaml;

trait InstallsModules
{
    public function enableModules($modules): self
    {
        parent::enableModules((array) $modules);

        return $this;
    }

    public function enableModuleWithDependencies($modules): self
    {
        foreach ((array) $modules as $module) {
            $fileLocation = drupal_get_path('module', $module) . '/' . $module . '.info.yml';

            $infoYaml = Yaml::decode(file_get_contents($fileLocation));

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
}
