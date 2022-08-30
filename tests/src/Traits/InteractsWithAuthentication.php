<?php

namespace Drupal\Tests\test_traits\Traits;

use Drupal\Core\Session\AccountInterface;
use Drupal\user\UserInterface;

trait InteractsWithAuthentication
{
    /** @var UserInterface */
    private $anonymousUser;

    /** Set the current user */
    public function actingAs(AccountInterface $user): self
    {
        $this->container->get('current_user')->setAccount($user);

        return $this;
    }

    public function actingAsAnonymous(): self
    {
        if (isset($this->anonymousUser) === false) {
            $userStorage = $this->container->get('entity_type.manager')->getStorage('user');

            $userStorage->create([
                'uid' => 0,
                'name' => 'anonymous',
                'status' => 1,
            ])->save();

            $this->anonymousUser = $userStorage->load(0);
        }

        $this->container->get('current_user')->setAccount($this->anonymousUser);

        return $this;
    }
}
