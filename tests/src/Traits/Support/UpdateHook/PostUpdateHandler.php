<?php

namespace Drupal\Tests\test_support\Traits\Support\UpdateHook;

use Drupal\Tests\test_support\Traits\Support\UpdateHook\Base\UpdateHookHandler;

class PostUpdateHandler extends UpdateHookHandler
{
    protected function getModuleName(): string
    {
        return '';
    }
}
