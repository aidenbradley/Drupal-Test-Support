<?php

namespace Drupal\Tests\test_support\Traits\Installs;

trait InstallsTheme
{
    /** @var bool */
    private $setup = false;

    /** @param string|string[] $themes */
    public function installThemes($themes): self
    {
        $this->setupThemeDependencies();

        foreach ((array) $themes as $theme) {
            $this->container
                ->get('theme_installer')
                ->install((array) $theme);

            $this->container
                ->get('config.factory')
                ->getEditable('system.theme')
                ->set('default', $theme)
                ->save();
        }

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
