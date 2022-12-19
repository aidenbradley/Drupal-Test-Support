<?php

namespace Drupal\Tests\test_support\Unit\Support\UpdateHook;

use Drupal\Tests\test_support\Traits\Support\UpdateHook\UpdateHandler;
use Drupal\Tests\UnitTestCase;

class UpdateHandlerTest extends UnitTestCase
{
    /** @test */
    public function get_module_name(): void
    {
        $handler = UpdateHandler::create('test_support_update_9401');
        $this->assertEquals('test_support', $handler->getModuleName());

        $handler = UpdateHandler::create('test_module_update_8001');
        $this->assertEquals('test_module', $handler->getModuleName());

        $handler = UpdateHandler::create('new_version_module_update_8101');
        $this->assertEquals('new_version_module', $handler->getModuleName());

        $handler = UpdateHandler::create('test_update_module_update_8111');
        $this->assertEquals('test_update_module', $handler->getModuleName());

        $handler = UpdateHandler::create('scheduler_module_update_9001');
        $this->assertEquals('scheduler_module', $handler->getModuleName());

        $handler = UpdateHandler::create('core_module_update_9101');
        $this->assertEquals('core_module', $handler->getModuleName());

        $handler = UpdateHandler::create('contrib_module_update_9111');
        $this->assertEquals('contrib_module', $handler->getModuleName());
    }

    /** @test */
    public function can_handle(): void
    {
        $this->assertTrue(UpdateHandler::canHandle('test_support_update_9401'));

        $this->assertFalse(UpdateHandler::canHandle('test_support_update_941'));
    }
}
