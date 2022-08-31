<?php

namespace Drupal\Tests\test_traits\Traits\Installs;

use Drupal\Core\Config\FileStorage;
use Drupal\Core\Site\Settings;
use Drupal\Tests\test_traits\Traits\Support\Exceptions\ConfigInstallFailed;
use Drupal\Tests\test_traits\Traits\Support\InteractsWithSettings;

trait InstallsExportedConfig
{
    use InstallsFields,
        InstallsImageStyles,
        InstallsRoles,
        InstallsVocabularies,
        InstallsEntityTypes,
        InstallsViews,
        InstallsBlocks,
        InstallsMenus,
        InteractsWithSettings;

    /** @var string */
    private $useVfsConfigDirectory = false;

    /** @var string */
    private $customConfigDirectory;

    /** @var array */
    private $installedConfig = [];

    /** @param string|array $config */
    public function installExportedConfig($config): self
    {
        $configStorage = new FileStorage($this->configDirectory());

        foreach ((array)$config as $configName) {
            if (in_array($configName, $this->installedConfig)) {
                continue;
            }

            $this->installedConfig[] = $configName;

            $configRecord = $configStorage->read($configName);

            if (is_array($configRecord) === false) {
                throw ConfigInstallFailed::doesNotExist($configName);
            }

            /** @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface $storage */
            $storage = $this->container->get('entity_type.manager')->getStorage(
                $this->container->get('config.manager')->getEntityTypeIdByName($configName)
            );

            $storage->createFromStorageRecord($configRecord)->save();
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
