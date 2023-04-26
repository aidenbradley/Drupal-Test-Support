<?php

namespace Drupal\Tests\test_support\Traits\Support;

use Drupal\Component\Utility\Random;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\RoleInterface;
use Drupal\user\UserInterface;

trait InteractsWithAuthentication
{
    /** @var UserInterface|null */
    private $anonymousUser = null;

    /** @param UserInterface|RoleInterface $user */
    public function actingAs($user): self
    {
        if ($user instanceof RoleInterface) {
            return $this->actingAsRole($user);
        }

        $this->container->get('current_user')->setAccount($user);

        return $this;
    }

    public function actingAsAnonymous(): self
    {
        if ($this->anonymousUser instanceof UserInterface === false) {
            $userStorage = $this->container->get('entity_type.manager')->getStorage('user');

            $userStorage->create([
                'uid' => 0,
                'name' => 'anonymous',
                'status' => 1,
            ])->save();

            $this->anonymousUser = $userStorage->load(0);
        }

        if ($this->anonymousUser instanceof AccountInterface) {
            $this->container->get('current_user')->setAccount($this->anonymousUser);
        }

        return $this;
    }

    public function actingAsRole(RoleInterface $role): self
    {
        $userStorage = $this->container->get('entity_type.manager')->getStorage('user');

        $user = $userStorage->create([
            'name' => (new Random())->string(),
        ]);
        $user->addRole((string) $role->id());
        $user->save();

        return $this->actingAs($user);
    }

    /** @param RoleInterface[] $roles */
    public function actingAsRoles(array $roles): self
    {
        $userStorage = $this->container->get('entity_type.manager')->getStorage('user');

        $user = $userStorage->create([
            'name' => (new Random())->string(),
        ]);

        foreach ($roles as $role) {
            $user->addRole((string) $role->id());
        }

        $user->save();

        return $this->actingAs($user);
    }
}
