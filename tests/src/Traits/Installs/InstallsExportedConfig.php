<?php

namespace Drupal\Tests\test_support\Traits\Installs;

use Drupal\Core\Config\FileStorage;
use Drupal\Core\Site\Settings;
use Drupal\Tests\test_support\Traits\Support\Exceptions\ConfigInstallFailed;
use Drupal\Tests\test_support\Traits\Support\InteractsWithSettings;

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
