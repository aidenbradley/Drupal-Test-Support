<?php

namespace Drupal\Tests\test_support\Traits\Installs;

use Drupal\Core\Config\FileStorage;
use Drupal\Tests\test_support\Traits\Installs\Configuration\InstallConfiguration;

trait InstallsFields
{
    use InstallConfiguration;

    /** @var bool */
    private $setupFieldDependencies = false;

    public function installField(string $fieldName, string $entityType, ?string $bundle = null): self
    {
        $this->setupFieldDependencies();

        return $this->installExportedConfig([
            'field.storage.' . $entityType . '.' . $fieldName,
            'field.field.' . $entityType . '.' . ($bundle ? $bundle . '.' : $entityType . '.') . $fieldName,
        ]);
    }

    public function installFields(array $fieldNames, string $entityType, ?string $bundle = null): self
    {
        $this->setupFieldDependencies();

        foreach ($fieldNames as $fieldName) {
            $this->installField($fieldName, $entityType, $bundle);
        }

        return $this;
    }

    public function installAllFieldsForEntity(string $entityType, ?string $bundle = null): self
    {
        $this->setupFieldDependencies();

        $configStorage = new FileStorage($this->configDirectory());

        return $this->installFields(array_map(function ($storageFieldName) {
            return substr($storageFieldName, strripos($storageFieldName, '.') + 1);
        }, $configStorage->listAll('field.storage.' . $entityType)), $entityType, $bundle);
    }

    private function setupFieldDependencies(): self
    {
        if ($this->setupFieldDependencies === false) {
            $this->enableModules(['field']);

            $this->setupFieldDependencies = true;
        }


        return $this;
    }
}
