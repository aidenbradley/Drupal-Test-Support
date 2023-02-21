<?php

namespace Drupal\Tests\test_support\Traits\Support\UpdateHook\Base;

use Drupal\Tests\test_support\Traits\Support\Exceptions\UpdateHookFailed;
use Drupal\Tests\test_support\Traits\Support\UpdateHook\Contracts\HookHandler;
use ReflectionFunction;

abstract class UpdateHookHandler implements HookHandler
{
    /** @var string */
    protected $function;

    /** Regex string to identify function name */
    abstract public static function pattern(): string;

    public static function create(string $function): HookHandler
    {
        return new static($function);
    }

    public function __construct(string $function)
    {
        $this->function = $function;
    }

    public function run(): HookHandler
    {
        $this->wantsBatch() ? $this->runAsBatch() : $this->runWithoutBatch();

        return $this;
    }

    public function getModuleName(): string
    {
        $matches = [];

        preg_match_all(static::pattern(), $this->function, $matches);

        return explode($matches[0][0], $this->function)[0];
    }

    public static function canHandle(string $function): bool
    {
        $matches = [];

        preg_match_all(static::pattern(), $function, $matches);

        return isset($matches[0]) && $matches[0] !== [];
    }

    private function runWithoutBatch(): void
    {
        if (is_callable($this->function) === false) {
            return;
        }

        call_user_func($this->function);
    }

    private function runAsBatch(): void
    {
        if (is_callable($this->function) === false) {
            return;
        }

        $batch = [
            '#finished' => 0,
        ];

        $lastBatchFinished = 0;

        do {
            call_user_func_array($this->function, [&$batch]);

            /** @var mixed $batchFinished */
            $batchFinished = $batch['#finished'];

            if (is_float($batchFinished) === false && is_int($batchFinished) === false) {
                throw UpdateHookFailed::invalidFinishedValue($batchFinished);
            }

            if ($batchFinished < 0 || $batchFinished > 1) {
                throw UpdateHookFailed::invalidFinishedValue($batchFinished);
            }

            if ($lastBatchFinished === $batchFinished) {
                throw UpdateHookFailed::noBatchProgression($batchFinished);
            }

            $lastBatchFinished = $batchFinished;
        } while ($batchFinished < 1);
    }

    private function wantsBatch(): bool
    {
        $reflection = new ReflectionFunction($this->function);

        return (bool) $reflection->getNumberOfParameters();
    }
}
