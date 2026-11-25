<?php

namespace Drupal\Tests\test_support\Traits\Support\Exceptions;

class UpdateHookFailed extends \Exception
{
    public const NO_BATCH_PROGRESSION = 1;

    public const INVALID_FINISHED_VALUE = 2;

    public const COULD_NOT_DETERMINE_MODULE_NAME = 3;

    public static function couldNotDetermineModuleName(string $function): self
    {
        $message = PHP_EOL . PHP_EOL . 'Could not determine the module name prefix for function: ' . $function;

        return new self($message, static::COULD_NOT_DETERMINE_MODULE_NAME);
    }

    /** @param  string|int|float  $finishedValue */
    public static function noBatchProgression($finishedValue): self
    {
        $message = PHP_EOL . PHP_EOL . 'The #finished value stayed at ' . $finishedValue . ' for two successive batch runs. It should strictly progress. Try the following:' .
            PHP_EOL . '$sandbox[\'#finished\'] = ($sandbox[\'current\'] / $sandbox[\'total\'])
        ';

        return new self($message, static::NO_BATCH_PROGRESSION);
    }

    /** @param  string|int|float  $finishedValue */
    public static function invalidFinishedValue($finishedValue): self
    {
        $message = PHP_EOL . PHP_EOL . 'The #finished value should be an integer or float between 0 and 1 but ' . $finishedValue . ' was returned.';

        return new self($message, static::INVALID_FINISHED_VALUE);
    }
}
