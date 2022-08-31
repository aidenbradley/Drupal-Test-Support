<?php

namespace Drupal\Tests\drupal_test_support\Traits\Installs;

trait InstallsTheme
{
    public function installTheme(string $theme): self
    {
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
}
