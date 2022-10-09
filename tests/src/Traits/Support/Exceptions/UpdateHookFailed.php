<?php

namespace Drupal\Tests\test_support\Traits\Support\Exceptions;

class UpdateHookFailed extends \Exception
{
    public const NO_BATCH_PROGRESSION = 1;

    public static function noBatchProgression(): self
    {
        $message = PHP_EOL . PHP_EOL . 'The #finished key did not progress between batch runs. Try something like:' .
            PHP_EOL . '$sandbox[\'#finished\'] = ($sandbox[\'current\'] / $sandbox[\'total\'])
        ';

        return new static($message, static::NO_BATCH_PROGRESSION);
    }
}
