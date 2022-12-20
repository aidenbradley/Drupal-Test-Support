<?php

namespace Drupal\Tests\test_support\Kernel\Support\UpdateHook;

use Drupal\Component\Utility\Random;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\test_support\Traits\Support\Exceptions\UpdateHookFailed;
use Drupal\Tests\test_support\Traits\Support\InteractsWithUpdateHooks;
use Drupal\Tests\test_support\Traits\Support\UpdateHook\Base\UpdateHookHandler;
use Drupal\Tests\test_support\Traits\Support\UpdateHook\Factory\HookHandlerFactory;
use Drupal\Tests\test_support\Traits\Support\UpdateHook\UpdateHandler;
use Drupal\user\Entity\User;

class UpdateHandlerTest extends KernelTestBase
{
    use InteractsWithUpdateHooks;

    protected static $modules = [
        'user',
    ];

    protected function setUp()
    {
        parent::setUp();

        $this->installEntitySchema('user');
    }

    /** @test */
    public function create_handler(): void
    {
        $handler = HookHandlerFactory::create('test_support_updatehooks_update_9002');

        $this->assertInstanceOf(UpdateHookHandler::class, $handler);
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

        $this->assertUsersNotBlocked(
            $this->storage('user')->loadMultiple()
        );

        $this->runUpdateHook('test_support_updatehooks_update_9002');

        $this->assertUsersBlocked(
            $this->storage('user')->loadMultiple()
        );
    }

    /** @test */
    public function runs_update_hook_with_batch(): void
    {
        $this->createNumberOfActiveUsers(50);

        $this->assertUsersNotBlocked(
            $this->storage('user')->loadMultiple()
        );

        $this->runUpdateHook('test_support_updatehooks_update_9001');

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

        $this->runUpdateHook('test_support_updatehooks_update_9003');
    }

    private function assertModuleDisabled(string $module): void
    {
        $this->assertFalse($this->container->get('module_handler')->moduleExists($module));
    }

    private function assertModuleEnabled(string $module): void
    {
        $this->assertTrue($this->container->get('module_handler')->moduleExists($module));
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
