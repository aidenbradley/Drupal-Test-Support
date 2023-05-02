<?php

namespace Drupal\Tests\test_support\Kernel\Support\UpdateHook;

use Drupal\Tests\test_support\Kernel\Support\UpdateHook\Base\UpdateHandlerKernelTestBase;
use Drupal\Tests\test_support\Traits\Support\Exceptions\UpdateHookFailed;
use Drupal\Tests\test_support\Traits\Support\InteractsWithUpdateHooks;

class UpdateHandlerTest extends UpdateHandlerKernelTestBase
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
        $this->assertModuleDisabled('test_support_updatehooks');

        $this->runUpdateHook('test_support_updatehooks_update_9002');

        $this->assertModuleEnabled('test_support_updatehooks');
    }

    /** @test */
    public function includes_install_file_when_update_hook_defined_in_install_file(): void
    {
        $this->assertFalse(function_exists('test_support_updatehooks_update_9002'));

        $this->runUpdateHook('test_support_updatehooks_update_9002');

        $this->assertTrue(function_exists('test_support_updatehooks_update_9002'));
    }

    /** @test */
    public function runs_update_hook_without_batch(): void
    {
        $this->createNumberOfActiveUsers(50);

        $this->assertUsersNotBlocked($this->loadAllUsers());

        $this->runUpdateHook('test_support_updatehooks_update_9002');

        $this->assertUsersBlocked($this->loadAllUsers());
    }

    /** @test */
    public function runs_update_hook_with_batch(): void
    {
        $this->createNumberOfActiveUsers(50);

        $this->assertUsersNotBlocked($this->loadAllUsers());

        $this->runUpdateHook('test_support_updatehooks_update_9001');

        $this->assertUsersBlocked($this->loadAllUsers());
    }

    /** @test */
    public function update_hook_with_batch_that_doesnt_increment_finished_key_triggers_exception(): void
    {
        $this->createNumberOfActiveUsers(50);

        $this->expectException(UpdateHookFailed::class);
        $this->expectExceptionCode(UpdateHookFailed::NO_BATCH_PROGRESSION);

        $this->runUpdateHook('test_support_updatehooks_update_9003');
    }
}
