<?php

namespace AidenBradley\DrupalTestSupport\Support\UpdateHook\Factory;

use AidenBradley\DrupalTestSupport\Support\UpdateHook\Contracts\HookHandler;
use AidenBradley\DrupalTestSupport\Support\UpdateHook\DeployHookHandler;
use AidenBradley\DrupalTestSupport\Support\UpdateHook\Exceptions\HookHandlerError;
use AidenBradley\DrupalTestSupport\Support\UpdateHook\PostUpdateHandler;
use AidenBradley\DrupalTestSupport\Support\UpdateHook\UpdateHandler;

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

        throw HookHandlerError::unableToHandle('unable to handle hook function "' . $function . '"');
    }
}
