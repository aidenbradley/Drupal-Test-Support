<?php

namespace Drupal\Tests\test_support\Traits\Support;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use PHPUnit\Framework\Assert;

trait InteractsWithEntities
{
    /** @param mixed[] $values */
    protected function createEntity(string $entityTypeId, array $values = []): EntityInterface
    {
        $entity = $this->storage($entityTypeId)->create($values);

        $entity->save();

        return $entity;
    }

    /** @param mixed[] $values */
    protected function updateEntity(EntityInterface $entity, array $values): EntityInterface
    {
        if (method_exists($entity, 'set')) {
            foreach ($values as $field => $value) {
                $entity->set($field, $value);
            }
        }

        $entity->save();

        return $entity;
    }

    protected function refreshEntity(EntityInterface &$entity): self
    {
        $reloaded = $this->storage($entity->getEntityTypeId())->load($entity->id());

        if ($reloaded === null) {
            Assert::fail('Could not reload entity with ID: ' . $entity->id());
        }

        $entity = $reloaded;

        return $this;
    }

    protected function storage(string $entityTypeId): EntityStorageInterface
    {
        return $this->container->get('entity_type.manager')->getStorage($entityTypeId);
    }
}
