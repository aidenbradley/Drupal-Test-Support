<?php

namespace Drupal\Tests\test_support\Kernel\Support\UpdateHook;

use Drupal\Component\Utility\Random;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\test_support\Traits\Support\Exceptions\UpdateHookFailed;
use Drupal\Tests\test_support\Traits\Support\InteractsWithUpdateHooks;
use Drupal\user\Entity\User;

class PostUpdateHandlerTest extends KernelTestBase
{
    use InteractsWithUpdateHooks;

    protected static $modules = [
        'system',
        'user',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->installEntitySchema('user');
        $this->installSchema('system', 'sequences');
    }

    /** @test */
    public function enables_module_that_defines_function(): void
    {
        $this->assertModuleDisabled('test_support_postupdatehooks');

        $this->runPostUpdateHook('test_support_postupdatehooks_post_update_no_batch_disable_users');

        $this->assertModuleEnabled('test_support_postupdatehooks');
    }

    /** @test */
    public function includes_post_update_file_when_post_update_hook_defined_in_post_update_file(): void
    {
        $this->assertFalse(function_exists('test_support_postupdatehooks_post_update_no_batch_disable_users'));

        $this->runPostUpdateHook('test_support_postupdatehooks_post_update_no_batch_disable_users');

        $this->assertTrue(function_exists('test_support_postupdatehooks_post_update_no_batch_disable_users'));
    }

    /** @test */
    public function runs_post_update_hook_without_batch(): void
    {
        $this->createNumberOfActiveUsers(50);

        $this->assertUsersNotBlocked(
            $this->storage('user')->loadMultiple()
        );

        $this->runPostUpdateHook('test_support_postupdatehooks_post_update_no_batch_disable_users');

        $this->assertUsersBlocked(
            $this->storage('user')->loadMultiple()
        );
    }

    /** @test */
    public function runs_post_update_hook_with_batch(): void
    {
        $this->createNumberOfActiveUsers(50);

        $this->assertUsersNotBlocked(
            $this->storage('user')->loadMultiple()
        );

        $this->runPostUpdateHook('test_support_postupdatehooks_post_update_with_batch_disable_users');

        $this->assertUsersBlocked(
            $this->storage('user')->loadMultiple()
        );
    }

    /** @test */
    public function update_hook_with_batch_that_doesnt_increment_finished_key_triggers_exception(): void
    {
        $this->createNumberOfActiveUsers(50);

        $this->expectException(UpdateHookFailed::class);
        $this->expectExceptionCode(UpdateHookFailed::NO_BATCH_PROGRESSION);

        $this->runPostUpdateHook('test_support_postupdatehooks_post_update_with_no_finished_progression');
    }

    private function assertModuleDisabled(string $module): void
    {
        $this->assertFalse($this->container->get('module_handler')->moduleExists($module));
    }

    private function assertModuleEnabled(string $module): void
    {
        $this->assertTrue($this->container->get('module_handler')->moduleExists($module));
    }

    /** @param  array|User  $users */
    private function assertUsersBlocked($users): self
    {
        foreach ((array) $users as $user) {
            $this->assertEquals(0, $user->get('status')->value);
        }

        return $this;
    }

    /** @param  array|User  $users */
    private function assertUsersNotBlocked($users): self
    {
        foreach ((array) $users as $user) {
            $this->assertEquals(1, $user->get('status')->value);
        }

        return $this;
    }

    private function storage(string $entityTypeId): EntityStorageInterface
    {
        return $this->container->get('entity_type.manager')->getStorage($entityTypeId);
    }

    private function createNumberOfActiveUsers(int $numberToCreate): void
    {
        for ($x = 0; $x <= $numberToCreate; $x++) {
            $this->storage('user')->create([
                'name' => (new Random())->string(),
                'status' => 1,
            ])->save();
        }
    }
}
