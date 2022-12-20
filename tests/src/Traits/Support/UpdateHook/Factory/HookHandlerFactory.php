<?php

namespace Drupal\Tests\test_support\Traits\Support\UpdateHook\Factory;

use Drupal\Tests\test_support\Traits\Support\UpdateHook\Contracts\HookHandler;
use Drupal\Tests\test_support\Traits\Support\UpdateHook\DeployHookHandler;
use Drupal\Tests\test_support\Traits\Support\UpdateHook\PostUpdateHandler;
use Drupal\Tests\test_support\Traits\Support\UpdateHook\UpdateHandler;

class HookHandlerFactory
{
    public static function create(string $function): HookHandler
    {
        if (DeployHookHandler::canHandle($function)) {
            return DeployHookHandler::create($function);
        }

        if (PostUpdateHandler::canHandle($function)) {
            return PostUpdateHandler::create($function);
        }

        if (UpdateHandler::canHandle($function)) {
            return UpdateHandler::create($function);
        }
    }
}
