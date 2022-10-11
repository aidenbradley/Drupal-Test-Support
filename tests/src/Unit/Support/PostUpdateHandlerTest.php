<?php

namespace Drupal\Tests\test_support\Unit\Support;

use Drupal\Tests\test_support\Traits\Support\UpdateHook\PostUpdateHandler;
use Drupal\Tests\test_support\Traits\Support\UpdateHook\UpdateHandler;
use Drupal\Tests\UnitTestCase;

class PostUpdateHandlerTest extends UnitTestCase
{
    /** @test */
    public function get_module_name_from_function(): void
    {
        $this->assertEquals(
            'test_module',
            PostUpdateHandler::create('test_module_post_update_custom_name')->getModuleName()
        );
        $this->assertEquals(
            'new_version_module',
            PostUpdateHandler::create('new_version_module_post_update_post_update')->getModuleName()
        );
        $this->assertEquals(
            'test_update_module',
            PostUpdateHandler::create('test_update_module_post_update_post_update_again')->getModuleName()
        );
        $this->assertEquals(
            'scheduler_module',
            PostUpdateHandler::create('scheduler_module_post_update_set_status')->getModuleName()
        );
        $this->assertEquals(
            'core_module',
            PostUpdateHandler::create('core_module_post_update_update_9001')->getModuleName()
        );
        $this->assertEquals(
            'contrib_module',
            PostUpdateHandler::create('contrib_module_post_update_update_9111')->getModuleName()
        );
        $this->assertEquals(
            'contrib_module',
            PostUpdateHandler::create('contrib_module_post_update_post_update_update_9111')->getModuleName()
        );
    }
}
