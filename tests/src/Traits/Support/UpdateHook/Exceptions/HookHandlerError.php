<?php

namespace Drupal\Tests\test_support\Traits\Support\UpdateHook\Exceptions;

class HookHandlerError extends \Exception
{
    public const UNABLE_TO_HANDLE = 1;

    public static function unableToHandle(string $message): self
    {
        return new static($message, static::UNABLE_TO_HANDLE);
    }
}
