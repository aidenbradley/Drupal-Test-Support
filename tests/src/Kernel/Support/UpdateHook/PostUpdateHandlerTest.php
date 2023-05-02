<?php

namespace Drupal\Tests\test_support\Kernel\Support\UpdateHook;

use Drupal\Tests\test_support\Kernel\Support\UpdateHook\Base\UpdateHandlerKernelTestBase;
use Drupal\Tests\test_support\Traits\Support\Exceptions\UpdateHookFailed;
use Drupal\Tests\test_support\Traits\Support\InteractsWithUpdateHooks;

class PostUpdateHandlerTest extends UpdateHandlerKernelTestBase
{
    use InteractsWithUpdateHooks;

    /** @var string[] */
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

        $this->assertUsersNotBlocked($this->loadAllUsers());

        $this->runPostUpdateHook('test_support_postupdatehooks_post_update_no_batch_disable_users');

        $this->assertUsersBlocked($this->loadAllUsers());
    }

    /** @test */
    public function runs_post_update_hook_with_batch(): void
    {
        $this->createNumberOfActiveUsers(50);

        $this->assertUsersNotBlocked($this->loadAllUsers());

        $this->runPostUpdateHook('test_support_postupdatehooks_post_update_with_batch_disable_users');

        $this->assertUsersBlocked($this->loadAllUsers());
    }

    /** @test */
    public function update_hook_with_batch_that_doesnt_increment_finished_key_triggers_exception(): void
    {
        $this->createNumberOfActiveUsers(50);

        $this->expectException(UpdateHookFailed::class);
        $this->expectExceptionCode(UpdateHookFailed::NO_BATCH_PROGRESSION);

        $this->runPostUpdateHook('test_support_postupdatehooks_post_update_with_no_finished_progression');
    }
}
