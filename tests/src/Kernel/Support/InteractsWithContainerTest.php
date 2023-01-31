<?php

namespace Drupal\Tests\test_support\Kernel\Support;

use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Routing\RouteSubscriber;
use Drupal\Tests\test_support\Traits\Support\InteractsWithContainer;


class InteractsWithContainerTest extends KernelTestBase
{
    use InteractsWithContainer;

    protected static $modules = [
        'node',
    ];

    /** @test */
    public function resolves_service_by_id(): void
    {
        $this->assertInstanceOf(RouteSubscriber::class, $this->service('node.route_subscriber'));
    }
}
