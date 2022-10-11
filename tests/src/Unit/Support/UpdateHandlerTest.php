<?php

namespace Drupal\Tests\test_support\Unit\Support;

use Drupal\Tests\test_support\Traits\Support\UpdateHook\UpdateHandler;
use Drupal\Tests\UnitTestCase;

class UpdateHandlerTest extends UnitTestCase
{
    /** @test */
    public function get_module_name_from_function(): void
    {
        $this->assertEquals(
            'test_module',
            UpdateHandler::create('test_module_update_8001')->getModuleName()
        );
        $this->assertEquals(
            'new_version_module',
            UpdateHandler::create('new_version_module_update_8101')->getModuleName()
        );
        $this->assertEquals(
            'test_update_module',
            UpdateHandler::create('test_update_module_update_8111')->getModuleName()
        );
        $this->assertEquals(
            'scheduler_module',
            UpdateHandler::create('scheduler_module_update_9001')->getModuleName()
        );
        $this->assertEquals(
            'core_module',
            UpdateHandler::create('core_module_update_9101')->getModuleName()
        );
        $this->assertEquals(
            'contrib_module',
            UpdateHandler::create('contrib_module_update_9111')->getModuleName()
        );
    }
}
