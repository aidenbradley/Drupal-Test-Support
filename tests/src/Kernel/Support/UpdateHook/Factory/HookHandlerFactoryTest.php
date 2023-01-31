<?php

namespace Drupal\Tests\test_support\Kernel\Support\UpdateHook\Factory;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\test_support\Traits\Support\UpdateHook\Base\UpdateHookHandler;
use Drupal\Tests\test_support\Traits\Support\UpdateHook\DeployHookHandler;
use Drupal\Tests\test_support\Traits\Support\UpdateHook\Exceptions\HookHandlerError;
use Drupal\Tests\test_support\Traits\Support\UpdateHook\Factory\HookHandlerFactory;
use Drupal\Tests\test_support\Traits\Support\UpdateHook\PostUpdateHandler;

class HookHandlerFactoryTest extends KernelTestBase
{
    /** @test */
    public function creates_deploy_hook_handler(): void
    {
        $handler = HookHandlerFactory::create('test_support_deployhooks_deploy_no_batch_disable_users');

        $this->assertInstanceOf(DeployHookHandler::class, $handler);
    }

    /** @test */
    public function creates_post_update_handler(): void
    {
        $handler = HookHandlerFactory::create('test_support_postupdatehooks_post_update_no_batch_disable_users');

        $this->assertInstanceOf(PostUpdateHandler::class, $handler);
    }

    /** @test */
    public function creates_update_hook_handler(): void
    {
        $handler = HookHandlerFactory::create('test_support_updatehooks_update_9002');

        $this->assertInstanceOf(UpdateHookHandler::class, $handler);
    }

    /** @test */
    public function exception_thrown_when_hook_cannot_be_determined(): void
    {
        $this->expectException(HookHandlerError::class);
        $this->expectExceptionCode(HookHandlerError::UNABLE_TO_HANDLE);

        HookHandlerFactory::create('foo_bar_function');
    }
}
