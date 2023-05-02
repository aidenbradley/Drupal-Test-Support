<?php

namespace Drupal\Tests\test_support\Traits\Installs;

use Drupal\Tests\test_support\Traits\Installs\Configuration\InstallConfiguration;

trait InstallsImageStyles
{
    use InstallConfiguration;

    /** @var bool */
    private $setupImageStyleDependencies = false;

    /** @param string|string[] $imageStyles */
    public function installImageStyles($imageStyles): self
    {
        $this->prepareImageStyleDependencies();

        foreach ((array) $imageStyles as $imageStyle) {
            $this->installExportedConfig([
                'image.style.' . $imageStyle,
            ]);
        }

        return $this;
    }

    private function prepareImageStyleDependencies(): self
    {
        if ($this->setupImageStyleDependencies === false) {
            $this->enableModules([
                'image',
            ]);

            $this->installEntitySchema('image_style');

            $this->setupImageStyleDependencies = true;
        }

        return $this;
    }
}
