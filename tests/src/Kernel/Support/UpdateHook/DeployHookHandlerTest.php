<?php

namespace Drupal\Tests\test_support\Kernel\Support\UpdateHook;

use Drupal\Component\Utility\Random;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\test_support\Traits\Support\Exceptions\UpdateHookFailed;
use Drupal\Tests\test_support\Traits\Support\InteractsWithUpdateHooks;
use Drupal\user\Entity\User;

class DeployHookHandlerTest extends KernelTestBase
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

    private function assertModuleDisabled(string $module): void
    {
        $this->assertFalse($this->container->get('module_handler')->moduleExists($module));
    }

    private function assertModuleEnabled(string $module): void
    {
        $this->assertTrue($this->container->get('module_handler')->moduleExists($module));
    }

    /** @param User[] $users */
    private function assertUsersBlocked(array $users): self
    {
        foreach ($users as $user) {
            $this->assertEquals(0, $user->get('status')->value);
        }

        return $this;
    }

    /** @param User[] $users */
    private function assertUsersNotBlocked(array $users): self
    {
        foreach ($users as $user) {
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

    /** @return User[] */
    private function loadAllUsers(): array
    {
        /** @phpstan-ignore-next-line */
        return $this->storage('user')->loadMultiple();
    }
}
