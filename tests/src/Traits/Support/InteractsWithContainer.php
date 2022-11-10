<?php

namespace Drupal\Tests\test_support\Traits\Support;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

trait InteractsWithContainer
{
    /**
     * Gets a service based on the service ID or the class string that represents the service ID.
     *
     * E.G.
     *
     * $this->service('node.route_subscriber');
     * $this->service('Drupal\node\Routing\RouteSubscriber::class');
     * $this->service(RouteSubscriber::class);
     *
     * @param string $id              The service identifier or class string
     * @param int    $invalidBehavior The behavior when the service does not exist
     *
     * @return object|null The associated service
     *
     * @throws InvalidArgumentException          when no definitions are available
     * @throws ServiceCircularReferenceException When a circular reference is detected
     * @throws ServiceNotFoundException          When the service is not defined
     * @throws \Exception
     *
     * @see Reference
     */
    public function service(string $serviceId)
    {
        if (class_exists($serviceId) && $this->container->has($serviceId) === false) {
            /** @var Definition $definition */
            foreach ($this->container->getDefinitions() as $definition) {
                if ($definition->getClass() !== $serviceId) {
                    continue;
                }

                $serviceId = $definition->getProperties()['_serviceId'];
            }
        }

        return $this->container->get($serviceId);
    }
}
