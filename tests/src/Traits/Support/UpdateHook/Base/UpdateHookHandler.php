<?php

namespace Drupal\Tests\test_support\Traits\Support\UpdateHook\Base;

use Drupal\Tests\test_support\Traits\Support\Exceptions\UpdateHookFailed;
use ReflectionFunction;

abstract class UpdateHookHandler
{
    /** @var string */
    private $function;

    /** returns the module name based on the given function string */
    abstract protected function getModuleName(): string;

    /** @return static */
    public static function handle(string $function)
    {
        return static::create($function)->run();
    }

    /** @return static */
    public static function create(string $function)
    {
        return new static($function);
    }

    public function __construct(string $function)
    {
        $this->function = $function;
    }

    /** @return static */
    private function run()
    {
        $this->wantsBatch() ? $this->runAsBatch() : $this->runWithoutBatch();

        return $this;
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
