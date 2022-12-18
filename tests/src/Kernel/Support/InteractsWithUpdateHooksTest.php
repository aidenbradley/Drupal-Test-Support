<?php

namespace Drupal\Tests\test_support\Kernel\Support;

use Drupal\Component\Utility\Random;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\test_support\Traits\Support\InteractsWithUpdateHooks;
use Drupal\user\Entity\User;

class InteractsWithUpdateHooksTest extends KernelTestBase
{
    use InteractsWithUpdateHooks;

    protected static $modules = [
        'user',
        'test_support_update_hooks',
    ];

    protected function setUp()
    {
        parent::setUp();

        $this->installEntitySchema('user');
    }

    /** @test */
    public function run_update_hook_with_batch(): void
    {
        $this->createNumberOfActiveUsers(50);

        $users = $this->container->get('entity_type.manager')->getStorage('user')->loadMultiple();

        foreach ($users as $user) {
            $this->assertUserNotBlocked($user);
        }

        $this->runUpdateHook('test_support_update_hooks_update_9001');

        foreach ($users as $user) {
            $this->assertUserBlocked($user);
        }
    }

    /** @test */
    public function run_update_hook_with_no_batch(): void
    {
        $this->createNumberOfActiveUsers(50);

        $users = $this->container->get('entity_type.manager')->getStorage('user')->loadMultiple();

        foreach ($users as $user) {
            $this->assertUserNotBlocked($user);
        }

        $this->runUpdateHook('test_support_update_hooks_update_9002');

        foreach ($users as $user) {
            $this->assertUserBlocked($user);
        }
    }

    /** @test */
    public function running_update_hook_enables_module_that_defines_function(): void
    {
        $this->disableModules([
            'test_support_update_hooks',
        ]);
        $this->assertFalse(
            $this->container->get('module_handler')->moduleExists('test_support_update_hooks')
        );

        $this->createNumberOfActiveUsers(50);

        $users = $this->container->get('entity_type.manager')->getStorage('user')->loadMultiple();

        foreach ($users as $user) {
            $this->assertUserNotBlocked($user);
        }

        $this->runUpdateHook('test_support_update_hooks_update_9002');

        $this->assertTrue(
            $this->container->get('module_handler')->moduleExists('test_support_update_hooks')
        );

        foreach ($users as $user) {
            $this->assertUserBlocked(
                User::load($user->id())
            );
        }
    }

    /** @test */
    public function run_post_update_hook_with_batch(): void
    {
        $this->createNumberOfActiveUsers(50);

        $users = $this->container->get('entity_type.manager')->getStorage('user')->loadMultiple();

        foreach ($users as $user) {
            $this->assertUserNotBlocked($user);
        }

        $this->runPostUpdateHook('test_support_update_hooks_post_update_batch_block_users');

        foreach ($users as $user) {
            $this->assertUserBlocked($user);
        }
    }

    /** @test */
    public function run_post_update_hook_with_no_batch(): void
    {
        $this->createNumberOfActiveUsers(50);

        $users = $this->container->get('entity_type.manager')->getStorage('user')->loadMultiple();

        foreach ($users as $user) {
            $this->assertUserNotBlocked($user);
        }

        $this->runPostUpdateHook('test_support_update_hooks_post_update_no_batch_block_users');

        foreach ($users as $user) {
            $this->assertUserBlocked($user);
        }
    }

    /** @test */
    public function running_post_update_enables_module_that_defines_function(): void
    {
        $this->disableModules([
            'test_support_update_hooks',
        ]);
        $this->assertFalse(
            $this->container->get('module_handler')->moduleExists('test_support_update_hooks')
        );

        $this->createNumberOfActiveUsers(50);

        $users = $this->container->get('entity_type.manager')->getStorage('user')->loadMultiple();

        foreach ($users as $user) {
            $this->assertUserNotBlocked($user);
        }

        $this->runPostUpdateHook('test_support_update_hooks_post_update_no_batch_block_users');

        $this->assertTrue(
            $this->container->get('module_handler')->moduleExists('test_support_update_hooks')
        );

        foreach ($users as $user) {
            $this->assertUserBlocked(
                User::load($user->id())
            );
        }
    }

    /** @test */
    public function run_deploy_hook_with_batch(): void
    {
        $this->createNumberOfActiveUsers(50);

        $users = $this->container->get('entity_type.manager')->getStorage('user')->loadMultiple();

        foreach ($users as $user) {
            $this->assertUserNotBlocked($user);
        }

        $this->runDeployHook('test_support_update_hooks_deploy_with_batch_disable_users');

        foreach ($users as $user) {
            $this->assertUserBlocked($user);
        }
    }

    /** @test */
    public function run_deploy_hook_with_no_batch(): void
    {
        $this->createNumberOfActiveUsers(50);

        $users = $this->container->get('entity_type.manager')->getStorage('user')->loadMultiple();

        foreach ($users as $user) {
            $this->assertUserNotBlocked($user);
        }

        $this->runDeployHook('test_support_update_hooks_deploy_no_batch_disable_users');

        foreach ($users as $user) {
            $this->assertUserBlocked($user);
        }
    }

    /** @test */
    public function running_deploy_enables_module_that_defines_function(): void
    {
        $this->disableModules([
            'test_support_update_hooks',
        ]);
        $this->assertFalse(
            $this->container->get('module_handler')->moduleExists('test_support_update_hooks')
        );

        $this->createNumberOfActiveUsers(50);

        $users = $this->container->get('entity_type.manager')->getStorage('user')->loadMultiple();

        foreach ($users as $user) {
            $this->assertUserNotBlocked($user);
        }

        $this->runDeployHook('test_support_update_hooks_deploy_no_batch_disable_users');

        $this->assertTrue(
            $this->container->get('module_handler')->moduleExists('test_support_update_hooks')
        );

        foreach ($users as $user) {
            $this->assertUserBlocked(
                User::load($user->id())
            );
        }
    }

    private function assertUserBlocked(User $user): void
    {
        $this->assertEquals(0, $user->get('status')->value);
    }

    private function assertUserNotBlocked(User $user): void
    {
        $this->assertEquals(1, $user->get('status')->value);
    }

    private function createNumberOfActiveUsers(int $numberToCreate): void
    {
        for ($x = 0; $x <= $numberToCreate; $x++) {
            $this->container->get('entity_type.manager')->getStorage('user')->create([
                'name' => (new Random())->string(),
                'status' => 1,
            ])->save();
        }
    }
}
