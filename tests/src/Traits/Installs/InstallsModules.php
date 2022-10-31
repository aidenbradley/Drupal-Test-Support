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
        $pathResolver = $this->container->get('extension.path.resolver');

        foreach ((array) $modules as $module) {
            $fileLocation = $pathResolver->getPath('module', $module) . '/' . $module . '.info.yml';

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
