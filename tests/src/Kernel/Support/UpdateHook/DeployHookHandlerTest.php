<?php

namespace Drupal\Tests\test_support\Kernel\Support\UpdateHook;

use Drupal\Tests\test_support\Kernel\Support\UpdateHook\Base\UpdateHandlerKernelTestBase;
use AidenBradley\DrupalTestSupport\Support\Exceptions\UpdateHookFailed;
use AidenBradley\DrupalTestSupport\Support\InteractsWithUpdateHooks;

class DeployHookHandlerTest extends UpdateHandlerKernelTestBase
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
        $this->assertModuleDisabled('test_support_deployhooks');

        $this->runDeployHook('test_support_deployhooks_deploy_no_batch_disable_users');

        $this->assertModuleEnabled('test_support_deployhooks');
    }

    /** @test */
    public function includes_deploy_file_when_deploy_hook_defined_in_deploy_file(): void
    {
        $this->assertFalse(function_exists('test_support_deployhooks_deploy_only_in_deploy_php'));

        $this->runDeployHook('test_support_deployhooks_deploy_only_in_deploy_php');

        $this->assertTrue(function_exists('test_support_deployhooks_deploy_only_in_deploy_php'));
    }

    /** @test */
    public function includes_install_file_when_deploy_hook_defined_in_install_file(): void
    {
        $this->assertFalse(function_exists('test_support_deployhooks_deploy_only_in_install_php'));

        $this->runDeployHook('test_support_deployhooks_deploy_only_in_install_php');

        $this->assertTrue(function_exists('test_support_deployhooks_deploy_only_in_install_php'));
    }

    /** @test */
    public function runs_deploy_hook_without_batch(): void
    {
        $this->createNumberOfActiveUsers(50);

        $this->assertUsersNotBlocked($this->loadAllUsers());

        $this->runDeployHook('test_support_deployhooks_deploy_no_batch_disable_users');

        $this->assertUsersBlocked($this->loadAllUsers());
    }

    /** @test */
    public function runs_deploy_hook_with_batch(): void
    {
        $this->createNumberOfActiveUsers(50);

        $this->assertUsersNotBlocked($this->loadAllUsers());

        $this->runDeployHook('test_support_deployhooks_deploy_with_batch_disable_users');

        $this->assertUsersBlocked($this->loadAllUsers());
    }

    /** @test */
    public function deploy_hook_with_batch_that_doesnt_increment_finished_key_triggers_exception(): void
    {
        $this->createNumberOfActiveUsers(50);

        $this->expectException(UpdateHookFailed::class);
        $this->expectExceptionCode(UpdateHookFailed::NO_BATCH_PROGRESSION);

        $this->runDeployHook('test_support_deployhooks_deploy_with_no_finished_progression');
    }
}
