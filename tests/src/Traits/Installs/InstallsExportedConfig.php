<?php

namespace Drupal\Tests\test_support\Traits\Installs;

trait InstallsExportedConfig
{
    use InstallsFields,
        InstallsImageStyles,
        InstallsRoles,
        InstallsVocabularies,
        InstallsEntityTypes,
        InstallsViews,
        InstallsBlocks,
        InstallsMenus;

    protected function disableStrictConfig(): self
    {
        $this->strictConfigSchema = false;

        return $this;
    }

    protected function enableStrictConfig(): self
    {
        $this->strictConfigSchema = true;

        return $this;
    }
}
