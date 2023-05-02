<?php

namespace AidenBradley\DrupalTestSupport\Installs;

use AidenBradley\DrupalTestSupport\Installs\Configuration\InstallConfiguration;

trait InstallsMenus
{
    use InstallConfiguration;

    /** @var bool */
    private $setupMenuDependencies = false;

    /** @param string|string[] $menus */
    public function installMenus($menus): self
    {
        $this->setupMenuDependencies();

        foreach ((array) $menus as $menu) {
            $this->installExportedConfig('system.menu.' . $menu);
        }

        return $this;
    }

    private function setupMenuDependencies(): self
    {
        if ($this->setupMenuDependencies === false) {
            $this->enableModules([
                'system',
            ]);

            $this->installEntitySchema('menu');

            $this->setupMenuDependencies = true;
        }

        return $this;
    }
}
