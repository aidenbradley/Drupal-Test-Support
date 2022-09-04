<?php

namespace Drupal\Tests\test_support\Traits\Support;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;

trait InteractsWithEntities
{
    protected function createEntity(string $entityTypeId, $values): EntityInterface
    {
        if (is_array($values)) {
            $entity = $this->storage($entityTypeId)->create($values);
        }

        $entity->save();

        return $entity;
    }

    protected function updateEntity(EntityInterface $entity, array $values): EntityInterface
    {
        foreach ($values as $field => $value) {
            $entity->set($field,  $value);
        }

        $entity->save();

        return $entity;
    }

    protected function refreshEntity(EntityInterface &$entity)
    {
        $entity = $this->storage($entity->getEntityTypeId())->load($entity->id());
    }

    protected function storage(string $entityTypeId): EntityStorageInterface
    {
        return $this->container->get('entity_type.manager')->getStorage($entityTypeId);
    }
}
