<?php

namespace Drupal\Tests\test_support\Kernel\Support;

use Drupal\Component\Utility\Random;
use Drupal\Core\Entity\EntityStorageInterface;
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

        $users = $this->storage('user')->loadMultiple();

        $this->assertUsersNotBlocked($users);

        $this->runUpdateHook('test_support_update_hooks_update_9001');

        $this->assertUsersBlocked($users);
    }

    /** @test */
    public function run_update_hook_with_no_batch(): void
    {
        $this->createNumberOfActiveUsers(50);

        $users = $this->storage('user')->loadMultiple();

        $this->assertUsersNotBlocked($users);

        $this->runUpdateHook('test_support_update_hooks_update_9002');

        $this->assertUsersBlocked($users);
    }

    /** @test */
    public function running_update_hook_enables_module_that_defines_function(): void
    {
        $this->disableModules([
            'test_support_update_hooks',
        ]);
        $this->assertModuleDisabled('test_support_update_hooks');

        $this->createNumberOfActiveUsers(50);

        $users = $this->storage('user')->loadMultiple();

        $this->assertUsersNotBlocked($users);

        $this->runUpdateHook('test_support_update_hooks_update_9002');

        $this->assertModuleEnabled('test_support_update_hooks');

        foreach ($users as $index => $user) {
            $users[$index] = User::load($user->id());
        }

        $this->assertUsersBlocked($users);
    }

    /** @test */
    public function run_post_update_hook_with_batch(): void
    {
        $this->createNumberOfActiveUsers(50);

        $users = $this->storage('user')->loadMultiple();

        $this->assertUsersNotBlocked($users);

        $this->runPostUpdateHook('test_support_update_hooks_post_update_batch_block_users');

        $this->assertUsersBlocked($users);
    }

    /** @test */
    public function run_post_update_hook_with_no_batch(): void
    {
        $this->createNumberOfActiveUsers(50);

        $users = $this->storage('user')->loadMultiple();

        $this->assertUsersNotBlocked($users);

        $this->runPostUpdateHook('test_support_update_hooks_post_update_no_batch_block_users');

        $this->assertUsersBlocked($users);
    }

    /** @test */
    public function running_post_update_enables_module_that_defines_function(): void
    {
        $this->disableModules([
            'test_support_update_hooks',
        ]);
        $this->assertModuleDisabled('test_support_update_hooks');

        $this->createNumberOfActiveUsers(50);

        $users = $this->storage('user')->loadMultiple();

        $this->assertUsersNotBlocked($users);

        $this->runPostUpdateHook('test_support_update_hooks_post_update_no_batch_block_users');

        $this->assertModuleEnabled('test_support_update_hooks');

        foreach ($users as $index => $user) {
            $users[$index] = User::load($user->id());
        }

        $this->assertUsersBlocked($users);
    }

    /** @test */
    public function run_deploy_hook_with_batch(): void
    {
        $this->createNumberOfActiveUsers(50);

        $users = $this->storage('user')->loadMultiple();

        $this->assertUsersNotBlocked($users);

        $this->runDeployHook('test_support_update_hooks_deploy_with_batch_disable_users');

        $this->assertUsersBlocked($users);
    }

    /** @test */
    public function run_deploy_hook_with_no_batch(): void
    {
        $this->createNumberOfActiveUsers(50);

        $users = $this->storage('user')->loadMultiple();

        $this->assertUsersNotBlocked($users);

        $this->runDeployHook('test_support_update_hooks_deploy_no_batch_disable_users');

        $this->assertUsersBlocked($users);
    }

    /** @test */
    public function running_deploy_enables_module_that_defines_function(): void
    {
        $this->disableModules([
            'test_support_update_hooks',
        ]);
        $this->assertModuleDisabled('test_support_update_hooks');

        $this->createNumberOfActiveUsers(50);

        $users = $this->storage('user')->loadMultiple();

        $this->assertUsersNotBlocked($users);

        $this->runDeployHook('test_support_update_hooks_deploy_no_batch_disable_users');

        $this->assertModuleEnabled('test_support_update_hooks');

        foreach ($users as $index => $user) {
            $users[$index] = User::load($user->id());
        }

        $this->assertUsersBlocked($users);
    }

    /** @param array|User $users */
    private function assertUsersBlocked($users): self
    {
        foreach((array) $users as $user) {
            $this->assertEquals(0, $user->get('status')->value);
        }

        return $this;
    }

    /** @param array|User $users */
    private function assertUsersNotBlocked($users): self
    {
        foreach((array) $users as $user) {
            $this->assertEquals(1, $user->get('status')->value);
        }

        return $this;
    }

    private function assertModuleDisabled(string $module): void
    {
        $this->assertFalse($this->container->get('module_handler')->moduleExists($module));
    }

    private function assertModuleEnabled(string $module): void
    {
        $this->assertTrue($this->container->get('module_handler')->moduleExists($module));
    }

    private function storage(string $entityTypeId): EntityStorageInterface
    {
        return $this->container->get('entity_type.manager')->getStorage($entityTypeId);
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
