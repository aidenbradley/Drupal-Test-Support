<?php

namespace Drupal\Tests\test_support\Traits\Installs;

trait InstallsExportedConfig
{
    use InstallsBlocks;
    use InstallsEntityTypes;
    use InstallsFields;
    use InstallsImageStyles;
    use InstallsMenus;
    use InstallsRoles;
    use InstallsViews;
    use InstallsVocabularies;

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
