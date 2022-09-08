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

    public static function createFromTravel(int $travel): self
    {
        return new self($travel);
    }

    public function __construct(int $travel)
    {
        $this->travel = $travel;
    }

    public function __call(string $method, array $args): void
    {
        $method = 'add' . ucfirst($method);

        Carbon::setTestNow(
            Carbon::now()->$method($this->travel)
        );
    }
}
