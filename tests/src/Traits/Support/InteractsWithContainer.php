<?php

namespace Drupal\Tests\test_support\Traits\Support;

use Symfony\Component\DependencyInjection\ContainerInterface;

trait InteractsWithContainer
{
    /**
     * Gets a service based on the service ID.
     *
     * E.G.
     *
     * $this->service('node.route_subscriber');
     *
     * @param string $id The service identifier or class string
     * @param int $invalidBehavior The behavior when the service does not exist
     * @return object|null The associated service
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Exception
     *
     * @see Reference
     */
    public function service($id, $invalidBehavior = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE)
    {
        return $this->container->get($id, $invalidBehavior);
    }
}
