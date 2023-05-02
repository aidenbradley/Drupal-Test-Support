<?php

namespace Drupal\Tests\test_support\Traits\Support\UpdateHook;

use Drupal\Tests\test_support\Traits\Support\UpdateHook\Base\UpdateHookHandler;

class UpdateHandler extends UpdateHookHandler
{
    public static function pattern(): string
    {
        return '(_update_\d{4})';
    }

    /** @return string[] */
    public static function requiredModuleFiles(): array
    {
        return [
            'install',
        ];
    }
}
