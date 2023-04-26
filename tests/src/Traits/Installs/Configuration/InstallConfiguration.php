<?php

namespace Drupal\Tests\test_support\Traits\Installs\Configuration;

use Drupal\Core\Config\FileStorage;
use Drupal\Core\Site\Settings;
use Drupal\Tests\test_support\Traits\Installs\InstallsTheme;
use Drupal\Tests\test_support\Traits\Support\Exceptions\ConfigInstallFailed;
use Drupal\Tests\test_support\Traits\Support\InteractsWithSettings;

trait InstallConfiguration
{
    use InstallsTheme;
    use InteractsWithSettings;

    /** @var bool */
    private $useVfsConfigDirectory = false;

    /** @var string */
    private $customConfigDirectory;

    /** @var array */
    private $installedConfig = [];

    /** @param  string|array  $config */
    public function installExportedConfig($config): self
    {
        $configStorage = new FileStorage($this->configDirectory());

        foreach ((array) $config as $configName) {
            if (in_array($configName, $this->installedConfig)) {
                continue;
            }

            $this->installedConfig[] = $configName;

            $configRecord = $configStorage->read($configName);

            if (is_array($configRecord) === false) {
                throw ConfigInstallFailed::doesNotExist($configName);
            }

            if ($this->strictConfigSchema) {
                if (isset($configRecord['dependencies']['module'])) {
                    $this->enableModules($configRecord['dependencies']['module']);
                }

                if (isset($configRecord['dependencies']['config'])) {
                    $this->installExportedConfig($configRecord['dependencies']['config']);
                }

                if (isset($configRecord['dependencies']['theme'])) {
                    $this->installThemes($configRecord['dependencies']['theme']);
                }
            }

            $entityType = $this->container->get('config.manager')->getEntityTypeIdByName($configName);

            if ($entityType) {
                $storage = $this->container->get('entity_type.manager')->getStorage($entityType);

//                if (is_array($configRecord) === false) {
//                    throw ConfigInstallFailed::couldNotHandle($configName);
//                }

                /** @phpstan-ignore-next-line */
                $storage->createFromStorageRecord($configRecord)->save();

                continue;
            }

            $this->container->get('config.factory')->getEditable($configName)->setData($configRecord)->save();
        }

        return $this;
    }

    protected function configDirectory(): string
    {
        if ($this->useVfsConfigDirectory) {
            return Settings::get('config_sync_directory');
        }

        if ($this->customConfigDirectory) {
            return '/' . ltrim($this->customConfigDirectory, '/');
        }

        return $this->getConfigurationDirectory();
    }

    /** sets the config directory relative to the __fixtures__ directory */
    protected function setConfigDirectory(string $directory): self
    {
        $this->customConfigDirectory = $directory;

        return $this;
    }

    protected function useVfsConfigDirectory(): self
    {
        $this->useVfsConfigDirectory = true;

        return $this;
    }
}
