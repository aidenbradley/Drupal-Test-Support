<?php

namespace Drupal\Tests\test_support\Traits\Support\UpdateHook;

use Drupal\Tests\test_support\Traits\Support\UpdateHook\Base\UpdateHookHandler;

class DeployHookHandler extends UpdateHookHandler
{
    public static function pattern(): string
    {
        return '(_deploy_)';
    }
}
