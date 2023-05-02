<?php

namespace AidenBradley\DrupalTestSupport\Support;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;

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
        $entity = $this->storage($entity->getEntityTypeId())->load($entity->id());

        return $this;
    }

    protected function storage(string $entityTypeId): EntityStorageInterface
    {
        return $this->container->get('entity_type.manager')->getStorage($entityTypeId);
    }
}
