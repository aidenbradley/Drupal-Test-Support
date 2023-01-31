<?php

namespace Drupal\Tests\test_support\Unit\Support\UpdateHook;

use Drupal\Tests\test_support\Traits\Support\UpdateHook\DeployHookHandler;
use Drupal\Tests\UnitTestCase;

class DeployHookHandlerTest extends UnitTestCase
{
    /** @test */
    public function get_module_name(): void
    {
        $handler = DeployHookHandler::create('test_support_deploy_hook');
        $this->assertEquals('test_support', $handler->getModuleName());

        $handler = DeployHookHandler::create('test_support_deploy_hook_deploy_again');
        $this->assertEquals('test_support', $handler->getModuleName());
    }

    /** @test */
    public function can_handle(): void
    {
        $this->assertTrue(DeployHookHandler::canHandle('test_support_deploy_disable_users'));
        $this->assertTrue(DeployHookHandler::canHandle('test_support_deploy_disable_users_deploy_test'));

        $this->assertFalse(DeployHookHandler::canHandle('test_support_disable_users'));
        $this->assertFalse(DeployHookHandler::canHandle('test_support_disable_users_test'));
    }
}
