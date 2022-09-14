<?php

namespace Drupal\Tests\test_support\Traits\Support\Time;

use Carbon\Carbon;

/**
 * @method void seconds
 * @method void minutes
 * @method void hours
 * @method void days
 * @method void months
 * @method void years
 */
class Tardis
{
    /** @var int */
    private $travel;

    public static function createFromTravel(?int $travel = null): self
    {
        return new self($travel);
    }

    public function back(): Carbon
    {
        Carbon::setTestNow();

        return Carbon::now();
    }

    public function __construct(?int $travel = null)
    {
        $this->travel = $travel;
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
    }
}
