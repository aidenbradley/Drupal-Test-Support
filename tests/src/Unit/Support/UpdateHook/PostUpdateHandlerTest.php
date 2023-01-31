<?php

namespace Drupal\Tests\test_support\Unit\Support\UpdateHook;

use Drupal\Tests\test_support\Traits\Support\UpdateHook\PostUpdateHandler;
use Drupal\Tests\UnitTestCase;

class PostUpdateHandlerTest extends UnitTestCase
{
    /** @test */
    public function get_module_name(): void
    {
        $handler = PostUpdateHandler::create('test_support_post_update_hook');
        $this->assertEquals('test_support', $handler->getModuleName());

        $handler = PostUpdateHandler::create('test_support_post_update_hook_post_update_again');
        $this->assertEquals('test_support', $handler->getModuleName());

        $handler = PostUpdateHandler::create('test_module_post_update_custom_name');
        $this->assertEquals('test_module', $handler->getModuleName());

        $handler = PostUpdateHandler::create('new_version_module_post_update_post_update');
        $this->assertEquals('new_version_module', $handler->getModuleName());

        $handler = PostUpdateHandler::create('test_update_module_post_update_post_update_again');
        $this->assertEquals('test_update_module', $handler->getModuleName());

        $handler = PostUpdateHandler::create('scheduler_module_post_update_set_status');
        $this->assertEquals('scheduler_module', $handler->getModuleName());

        $handler = PostUpdateHandler::create('core_module_post_update_update_9001');
        $this->assertEquals('core_module', $handler->getModuleName());

        $handler = PostUpdateHandler::create('contrib_module_post_update_update_9111');
        $this->assertEquals('contrib_module', $handler->getModuleName());

        $handler = PostUpdateHandler::create('contrib_module_post_update_post_update_update_9111');
        $this->assertEquals('contrib_module', $handler->getModuleName());
    }

    /** @test */
    public function can_handle(): void
    {
        $this->assertTrue(PostUpdateHandler::canHandle('test_support_post_update_disable_users'));
        $this->assertTrue(PostUpdateHandler::canHandle('test_support_post_update_disable_users_post_update_test'));

        $this->assertFalse(PostUpdateHandler::canHandle('test_support_disable_users'));
        $this->assertFalse(PostUpdateHandler::canHandle('test_support_disable_users_test'));
    }
}
