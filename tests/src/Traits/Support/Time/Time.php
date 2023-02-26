<?php

namespace Drupal\Tests\test_support\Traits\Support\Time;

use Carbon\Carbon;
use Drupal\Component\Datetime\TimeInterface;

class Time implements TimeInterface
{
    public static function fake(): self
    {
        return new self();
    }

    public function getRequestTime(): int
    {
        return Carbon::now()->getTimestamp();
    }

    /** @return int|float */
    public function getRequestMicroTime()
    {
        return Carbon::now()->getTimestampMs();
    }

    public function getCurrentTime(): int
    {
        return Carbon::now()->getTimestamp();
    }

    /** @return int|float */
    public function getCurrentMicroTime()
    {
        return Carbon::now()->getTimestampMs();
    }
}
