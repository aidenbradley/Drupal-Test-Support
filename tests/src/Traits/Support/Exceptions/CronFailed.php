<?php

namespace AidenBradley\DrupalTestSupport\Support\Exceptions;

class CronFailed extends \Exception
{
    public const NO_CRON_KEY_SET = 1;

    public static function noCronKey(): self
    {
        return new static(
            'No cron key has been set. Use `$this->setCronKey()` to set one.',
            static::NO_CRON_KEY_SET
        );
    }
}
