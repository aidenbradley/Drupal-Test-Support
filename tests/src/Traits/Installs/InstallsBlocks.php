<?php

namespace Drupal\Tests\test_support\Traits\Installs;

use Drupal\Tests\test_support\Traits\Installs\Configuration\InstallConfiguration;

trait InstallsBlocks
{
    use InstallConfiguration;

    /** @var bool */
    private $setupBlockDependencies = false;

    /** @param string|string[] $blocks */
    public function installBlocks($blocks): void
    {
        $this->setupBlockDependencies();

        foreach ((array) $blocks as $block) {
            $this->installExportedConfig('block.block.' . $block);
        }
    }

    private function setupBlockDependencies(): self
    {
        if ($this->setupBlockDependencies === false) {
            $this->enableModules([
                'system',
                'block',
            ]);

            $this->installEntitySchema('block');

            $this->setupBlockDependencies = true;
        }

        return $this;
    }
}
