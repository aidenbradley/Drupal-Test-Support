<?php

namespace Drupal\Tests\test_support\Traits\Support\Mock;
use Drupal\Component\Datetime\TimeInterface;

class Time implements TimeInterface
{
    /** @var int */
    private $timestamp;

    public static function create(int $timestamp)
    {
        return new self($timestamp);
    }

    public function __construct(int $timestamp)
    {
        $this->timestamp = $timestamp;
    }

    public function getRequestTime(): int
    {
        return $this->timestamp;
    }

    public function getRequestMicroTime(): int
    {
        return $this->timestamp;
    }

    public function getCurrentTime(): int
    {
        return $this->timestamp;
    }

    public function getCurrentMicroTime(): int
    {
        return $this->timestamp;
    }
}
