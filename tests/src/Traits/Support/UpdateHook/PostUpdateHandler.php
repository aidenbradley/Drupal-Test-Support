<?php

namespace Drupal\Tests\test_support\Traits\Support\UpdateHook;

use Drupal\Tests\test_support\Traits\Support\UpdateHook\Base\UpdateHookHandler;

class PostUpdateHandler extends UpdateHookHandler
{
    public static function pattern(): string
    {
        return '(_post_update_)';
    }

    public static function requiredModuleFiles(): array
    {
        return [
            'post_update.php',
        ];
    }
}
