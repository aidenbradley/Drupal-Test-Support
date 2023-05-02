<?php

namespace AidenBradley\DrupalTestSupport\Support\UpdateHook;

use AidenBradley\DrupalTestSupport\Support\UpdateHook\Base\UpdateHookHandler;

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
