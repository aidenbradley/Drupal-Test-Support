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

    public function run(): self
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
        call_user_func($this->function);
    }

    private function runAsBatch(): void
    {
        $batch = [
            '#finished' => 0,
        ];

        while ($batch['#finished'] !== 1) {
            $progressBefore = $batch['#finished'];

            call_user_func_array($this->function, [&$batch]);

            $progressAfter  = $batch['#finished'];

            if ($progressBefore !== $progressAfter) {
                continue;
            }

            throw UpdateHookFailed::noBatchProgression();
        }
    }

    private function wantsBatch(): bool
    {
        $reflection = new ReflectionFunction($this->function);

        return $reflection->getNumberOfParameters();
    }
}
