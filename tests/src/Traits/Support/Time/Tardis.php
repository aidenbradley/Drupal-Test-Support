<?php

namespace Drupal\Tests\test_support\Traits\Support\Time;

use Carbon\Carbon;

/**
 * @method void seconds(?\Closure $callback = null)
 * @method void minutes(?\Closure $callback = null)
 * @method void hours(?\Closure $callback = null)
 * @method void days(?\Closure $callback = null)
 * @method void weeks(?\Closure $callback = null)
 * @method void months(?\Closure $callback = null)
 * @method void years(?\Closure $callback = null)
 */
class Tardis
{
    /**
     * @phpstan-ignore-next-line
     * @var \Drupal\Component\DependencyInjection\ContainerInterface|\Drupal\Core\DependencyInjection\ContainerBuilder @container
     */
    private $container;

    /** @var int|null */
    private $travel;

    /**
     * @phpstan-ignore-next-line
     * @param  \Drupal\Component\DependencyInjection\ContainerInterface|\Drupal\Core\DependencyInjection\ContainerBuilder  $container
     */
    public static function createFromTravel($container, ?int $travel = null): self
    {
        return new self($container, $travel);
    }

    /**
     * @phpstan-ignore-next-line
     * @param  \Drupal\Component\DependencyInjection\ContainerInterface|\Drupal\Core\DependencyInjection\ContainerBuilder  $container
     */
    public function __construct($container, ?int $travel = null)
    {
        $this->container = $container;
        $this->travel = $travel;
    }

    public function back(): void
    {
        Carbon::setTestNow();
    }

    public function toTimezone(string $timezone, ?callable $callback = null): void
    {
        $currentTimezone = Carbon::now()->getTimezone()->getName();

        $this->setTimezone($timezone);

        if ($callback === null) {
            return;
        }

        $this->freezeTime($callback);

        $this->setTimezone($currentTimezone);
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

    private function setTimezone(string $timezone): void
    {
        Carbon::setTestNowAndTimezone(
            Carbon::now()->setTimezone($timezone)
        );

        /** @phpstan-ignore-next-line */
        $this->container->get('config.factory')
            ->getEditable('system.date')
            ->set('timezone.default', $timezone)
            ->save();
    }

    /** @param array{0: ?callable} $args */
    public function __call(string $method, array $args): void
    {
        if ($this->travel === null) {
            return;
        }

        $method = 'add' . ucfirst($method);

        if (method_exists(Carbon::class, $method) === false) {
            return;
        }

        Carbon::setTestNowAndTimezone(
            Carbon::now()->$method($this->travel),
            Carbon::now()->getTimezone()
        );

        if (isset($args[0]) === false) {
            return;
        }

        $this->freezeTime($args[0]);
    }
}
