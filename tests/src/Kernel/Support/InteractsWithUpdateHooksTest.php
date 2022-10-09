<?php

namespace Drupal\Tests\test_support\Kernel\Support;

use Drupal\Component\Utility\Random;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\test_support\Traits\Support\InteractsWithUpdateHooks;
use Drupal\user\Entity\User;
use ReflectionFunction;

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
        for ($x = 0; $x <= 50; $x++) {
            $this->container->get('entity_type.manager')->getStorage('user')->create([
                'name' => (new Random())->string(),
                'status' => 1,
            ])->save();
        }

        $users = $this->container->get('entity_type.manager')->getStorage('user')->loadMultiple();

        foreach ($users as $user) {
            $this->assertUserNotBlocked($user);
        }

        $this->runUpdateHook('test_support_update_hooks', 'test_support_update_hooks_update_9001');

        foreach ($users as $user) {
            $this->assertUserBlocked($user);
        }
    }

    /** @test */
    public function run_update_hook_with_no_batch(): void
    {
        for ($x = 0; $x <= 50; $x++) {
            $this->container->get('entity_type.manager')->getStorage('user')->create([
                'name' => (new Random())->string(),
                'status' => 1,
            ])->save();
        }

        $users = $this->container->get('entity_type.manager')->getStorage('user')->loadMultiple();

        foreach ($users as $user) {
            $this->assertUserNotBlocked($user);
        }

        $this->runUpdateHook('test_support_update_hooks', 'test_support_update_hooks_update_9002');

        foreach ($users as $user) {
            $this->assertUserBlocked($user);
        }
    }

    /** @test */
    public function run_post_update_hook_with_batch(): void
    {
        for ($x = 0; $x <= 50; $x++) {
            $this->container->get('entity_type.manager')->getStorage('user')->create([
                'name' => (new Random())->string(),
                'status' => 1,
            ])->save();
        }

        $users = $this->container->get('entity_type.manager')->getStorage('user')->loadMultiple();

        foreach ($users as $user) {
            $this->assertUserNotBlocked($user);
        }

        $this->runPostUpdateHook(
            'test_support_update_hooks',
            'test_support_update_hooks_post_update_batch_block_users'
        );

        foreach ($users as $user) {
            $this->assertUserBlocked($user);
        }
    }

    /** @test */
    public function run_post_update_hook_with_no_batch(): void
    {
        for ($x = 0; $x <= 50; $x++) {
            $this->container->get('entity_type.manager')->getStorage('user')->create([
                'name' => (new Random())->string(),
                'status' => 1,
            ])->save();
        }

        $users = $this->container->get('entity_type.manager')->getStorage('user')->loadMultiple();

        foreach ($users as $user) {
            $this->assertUserNotBlocked($user);
        }

        $this->runPostUpdateHook(
            'test_support_update_hooks',
            'test_support_update_hooks_post_update_no_batch_block_users'
        );

        foreach ($users as $user) {
            $this->assertUserBlocked($user);
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
}
