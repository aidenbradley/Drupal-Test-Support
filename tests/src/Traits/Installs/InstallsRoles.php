<?php

namespace Drupal\Tests\test_support\Traits\Installs;

use Drupal\Tests\test_support\Traits\Installs\Configuration\InstallConfiguration;

trait InstallsRoles
{
    use InstallConfiguration;

    /** @var bool */
    private $setupRoleDependencies = false;

    /** @param  string|array  $roles */
    public function installRoles($roles): self
    {
        $this->setupRoleDependencies();

        foreach ((array) $roles as $role) {
            $this->installExportedConfig('user.role.' . $role);
        }

        return $this;
    }

    private function setupRoleDependencies(): self
    {
        if ($this->setupRoleDependencies === false) {
            $this->enableModules([
                'system',
                'user',
            ]);

            $this->installEntitySchema('user_role');

            $this->setupRoleDependencies = true;
        }

        return $this;
    }
}
