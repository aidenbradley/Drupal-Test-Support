<?php

namespace Drupal\Tests\test_support\Traits\Support\Time;

use Carbon\Carbon;

/**
 * @method void seconds(?\Closure $callback = null)
 * @method void minutes(?\Closure $callback = null)
 * @method void hours(?\Closure $callback = null)
 * @method void days(?\Closure $callback = null)
 * @method void months(?\Closure $callback = null)
 * @method void years(?\Closure $callback = null)
 */
class Tardis
{
    /** @var int */
    private $travel;

    public static function createFromTravel(?int $travel = null): self
    {
        return new self($travel);
    }

    public function __construct(?int $travel = null)
    {
        $this->travel = $travel;
    }

    public function back(): Carbon
    {
        Carbon::setTestNow();

        return Carbon::now();
    }

    public function toTimezone(string $timezone, ?callable $callback = null): void
    {
        Carbon::setTestNowAndTimezone(
            Carbon::now()->setTimezone($timezone)
        );

        if ($callback === null) {
            return;
        }

        $this->freezeTime($callback);
    }

    public function __call(string $method, array $args): void
    {
        if ($this->travel === null) {
            return;
        }

        $method = 'add' . ucfirst($method);

        Carbon::setTestNowAndTimezone(
            Carbon::now()->$method($this->travel),
            Carbon::now()->getTimezone()
        );

        if (isset($args[0]) === false) {
            return;
        }

        $this->freezeTime($args[0]);
    }

    /** @test */
    public function freezeTime(?callable $callback = null): void
    {
        if (is_callable($callback) === false) {
            return;
        }

        $callback();

        $this->back();
    }
}
