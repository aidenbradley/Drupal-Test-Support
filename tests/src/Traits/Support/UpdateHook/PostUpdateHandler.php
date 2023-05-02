<?php

namespace AidenBradley\DrupalTestSupport\Support\UpdateHook;

use AidenBradley\DrupalTestSupport\Support\UpdateHook\Base\UpdateHookHandler;

class PostUpdateHandler extends UpdateHookHandler
{
    public static function pattern(): string
    {
        return '(_post_update_)';
    }

    /** @return string[] */
    public static function requiredModuleFiles(): array
    {
        return [
            'post_update.php',
        ];
    }
}
