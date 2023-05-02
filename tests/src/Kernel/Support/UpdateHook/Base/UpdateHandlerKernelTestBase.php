<?php

namespace Drupal\Tests\test_support\Kernel\Support\UpdateHook\Base;

use Drupal\Component\Utility\Random;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\user\Entity\User;

abstract class UpdateHandlerKernelTestBase extends KernelTestBase
{
    protected function assertModuleDisabled(string $module): void
    {
        $this->assertFalse($this->container->get('module_handler')->moduleExists($module));
    }

    protected function assertModuleEnabled(string $module): void
    {
        $this->assertTrue($this->container->get('module_handler')->moduleExists($module));
    }

    /** @param User[] $users */
    protected function assertUsersBlocked(array $users): self
    {
        foreach ($users as $user) {
            $this->assertEquals(0, $user->get('status')->value);
        }

        return $this;
    }

    /** @param User[] $users */
    protected function assertUsersNotBlocked(array $users): self
    {
        foreach ($users as $user) {
            $this->assertEquals(1, $user->get('status')->value);
        }

        return $this;
    }

    protected function storage(string $entityTypeId): EntityStorageInterface
    {
        return $this->container->get('entity_type.manager')->getStorage($entityTypeId);
    }

    protected function createNumberOfActiveUsers(int $numberToCreate): void
    {
        for ($x = 0; $x <= $numberToCreate; $x++) {
            $this->storage('user')->create([
                'name' => (new Random())->string(),
                'status' => 1,
            ])->save();
        }
    }

    /** @return User[] */
    protected function loadAllUsers(): array
    {
        /** @phpstan-ignore-next-line */
        return $this->storage('user')->loadMultiple();
    }
}
