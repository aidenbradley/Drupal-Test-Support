<?php

namespace AidenBradley\DrupalTestSupport\Support\UpdateHook;

use AidenBradley\DrupalTestSupport\Support\UpdateHook\Base\UpdateHookHandler;

class DeployHookHandler extends UpdateHookHandler
{
    public static function pattern(): string
    {
        return '(_deploy_)';
    }

    /** @return string[] */
    public static function requiredModuleFiles(): array
    {
        return [
            'install',
            'deploy.php',
        ];
    }
}
