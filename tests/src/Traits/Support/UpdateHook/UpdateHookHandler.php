<?php

namespace Drupal\Tests\test_support\Traits\Support\UpdateHook;

use Drupal\Tests\test_support\Traits\Support\Exceptions\UpdateHookFailed;
use ReflectionFunction;

class UpdateHookHandler
{
    /** @var string */
    private $function;

    public static function handle(string $function): self
    {
        return self::create($function)->run();
    }

    public static function create(string $function): self
    {
        return new self($function);
    }

    public function __construct(string $function)
    {
        $this->function = $function;
    }

    private function run(): self
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
