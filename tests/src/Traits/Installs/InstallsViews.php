<?php

namespace AidenBradley\DrupalTestSupport\Installs;

use AidenBradley\DrupalTestSupport\Installs\Configuration\InstallConfiguration;

trait InstallsViews
{
    use InstallConfiguration;

    /** @var bool */
    private $setupViewsDependencies = false;

    /** @param string|string[] $views */
    public function installViews($views): self
    {
        $this->setupViewsDependencies();

        foreach ((array) $views as $view) {
            $this->installExportedConfig('views.view.' . $view);
        }

        return $this;
    }

    private function setupViewsDependencies(): self
    {
        if ($this->setupViewsDependencies === false) {
            $this->enableModules([
                'system',
                'user',
                'views',
            ]);

            $this->installEntitySchema('view');

            $this->setupViewsDependencies = true;
        }

        return $this;
    }
}
