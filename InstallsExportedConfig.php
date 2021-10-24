<?php

namespace Drupal\helpers\Concerns\Tests;

use Drupal\Core\Config\FileStorage;

/**
 * This trait may be used to test fields stored as field configs
 * Your test may need ::$strictConfigSchema = false; to work
 */
trait InstallsExportedConfig
{
    /** @var array */
    protected $installedConfig = [];

    /** @var bool */
    protected $installFieldModule;

    public function installExportedFields(array $fieldNames, string $entityType, ?string $bundle = null): void
    {
        foreach ($fieldNames as $fieldName) {
            $this->installExportedField($fieldName, $entityType, $bundle);
        }
    }

    public function installExportedField(string $fieldName, string $entityType, ?string $bundle = null): void
    {
        if (isset($this->installFieldModule) === false) {
            $this->enableModules(['field']);
            $this->installFieldModule = true;
        }

        $this->installExportedConfig([
            'field.storage.' . $entityType . '.' . $fieldName,
            'field.field.' . $entityType . '.' . ($bundle ? $bundle . '.' : $entityType . '.') . $fieldName,
        ]);
    }

    public function installAllFieldsForEntity(string $entityType, ?string $bundle = null): void
    {
        $configStorage = new FileStorage(
            str_replace('web/', '', \Drupal::service('app.root') . '/config/sync')
        );

        $this->installExportedFields(array_map(function ($storageFieldName) {
            return substr($storageFieldName, strripos($storageFieldName, '.') + 1);
        }, $configStorage->listAll("field.storage.$entityType")), $entityType, $bundle);
    }
    
    public function installExportedImageStyle(string $imageStyle): void
    {
        $this->installExportedConfig([
            'image.style.' . $imageStyle,
        ]);
    }

    public function installExportedBundle(string $module, string $bundle): void
    {
        $this->installExportedConfig([
            $module . '.type.' . $bundle,
        ]);
    }

    public function installExportedBundles(string $entityType, array $bundles): void
    {
        foreach ($bundles as $bundle) {
            $this->installExportedBundle($entityType, $bundle);
        }
    }

    /** @param string|array $bundles */
    public function installEntitySchemaWithBundles(string $entityType, $bundles): void
    {
        $this->installEntitySchema($entityType);

        $this->installExportedBundles($entityType, (array) $bundles);
    }

    public function installExportedVocabulary(string $vocabularyName): void
    {
        $this->installExportedConfig([
            'taxonomy.vocabulary.' . $vocabularyName,
        ]);
    }

    public function installExportedConfig($config): void
    {
        $configStorage = new FileStorage(
            str_replace('web/', '', \Drupal::service('app.root') . '/config/sync')
        );

        foreach ((array) $config as $configName) {
            if (in_array($configName, $this->installedConfig)) {
                continue;
            }

            $this->installedConfig[] = $configName;

            $configRecord = $configStorage->read($configName);

            $entityType = \Drupal::service('config.manager')->getEntityTypeIdByName($configName);

            /** @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface $storage */
            $storage = \Drupal::entityTypeManager()->getStorage($entityType);

            if (is_array($configRecord) === false) {
                return;
            }

            $storage->createFromStorageRecord($configRecord)->save();
        }
    }

    public function installRole(string $role): void
    {
        $this->installExportedConfig('user.role.' . $role);
    }
}
