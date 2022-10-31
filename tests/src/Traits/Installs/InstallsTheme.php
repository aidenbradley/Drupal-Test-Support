<?php

namespace Drupal\Tests\test_support\Traits\Installs;

trait InstallsTheme
{
    private $setup = false;

    public function installTheme(string $theme): self
    {
        $this->setupThemeDependencies();

        $this->container
            ->get('theme_installer')
            ->install((array) $theme);

        $this->container
            ->get('config.factory')
            ->getEditable('system.theme')
            ->set('default', $theme)
            ->save();

        $this->container->set('theme.registry', null);

        return $this;
    }

    private function setupThemeDependencies(): void
    {
        if ($this->setup) {
            return;
        }

        $this->setup = true;

        $this->enableModules([
            'system',
        ]);

        $this->installConfig('system');
    }
}
