<?php

namespace Drupal\Tests\test_support\Kernel\Support;

use Drupal\Component\Utility\Random;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\test_support\Kernel\Support\Concerns\UpdateHookTests;
use Drupal\Tests\test_support\Traits\Support\Exceptions\UpdateHookFailed;
use Drupal\Tests\test_support\Traits\Support\UpdateHook\UpdateHandler;
use Drupal\Tests\test_support\Traits\Support\UpdateHook\UpdateHookHandler;

class UpdateHandlerTest extends KernelTestBase
{
    protected static $modules = [
        'user',
    ];

    protected function setUp()
    {
        parent::setUp();

        $this->installEntitySchema('user');

        require '__fixtures__/functions/update_hook_functions.php';
    }

    /** @test */
    public function runs_update_hook_without_batch(): void
    {
        $this->assertNull($this->container->get('state')->get('no_batch_update_hook'));

        UpdateHandler::handle('no_batch_update_hook');

        $this->assertNotNull($this->container->get('state')->get('no_batch_update_hook'));
    }

    /** @test */
    public function runs_update_hook_with_batch(): void
    {
        $this->createNumberOfUsers(50);

        $this->assertNull($this->container->get('state')->get('batch_update_hook'));

        UpdateHandler::handle('batch_update_hook');

        $this->assertEquals(50, $this->container->get('state')->get('batch_update_hook'));
    }

    /** @test */
    public function update_hook_with_batch_that_doesnt_increment_finished_key_triggers_exception(): void
    {
        $this->createNumberOfUsers(50);

        $this->expectException(UpdateHookFailed::class);
        $this->expectExceptionCode(UpdateHookFailed::NO_BATCH_PROGRESSION);

        UpdateHandler::handle('batch_update_hook_with_no_finished_progression');
    }

    private function createNumberOfUsers(int $numberToCreate): void
    {
        for ($x = 0; $x <= $numberToCreate; $x++) {
            $this->container->get('entity_type.manager')->getStorage('user')->create([
                'name' => (new Random())->string(),
            ])->save();
        }
    }
}
