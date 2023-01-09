<?php

namespace Drupal\test_support_time\Controller;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class TimeController implements ContainerInjectionInterface
{
    /** @var TimeInterface */
    private $time;

    public static function create(ContainerInterface $container): self
    {
        return new self(
            $container->get('datetime.time')
        );
    }

    public function __construct(TimeInterface $time)
    {
        $this->time = $time;
    }

    public function requestTime(): JsonResponse
    {
        return new JsonResponse([
            'request_time' => $this->time->getRequestTime(),
        ]);
    }
}
