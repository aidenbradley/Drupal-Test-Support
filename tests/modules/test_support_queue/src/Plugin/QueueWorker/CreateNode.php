<?php

namespace Drupal\test_support_queue\Plugin\QueueWorker;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @QueueWorker(
 *   id = "create_node_worker",
 *   title = @Translation("Creates nodes based on the title data stored in the queue"),
 *   cron = {"time" = 10}
 * )
 */
class CreateNode extends QueueWorkerBase implements ContainerFactoryPluginInterface
{
    /** @var EntityTypeManager */
    private $entityTypeManager;

    /**
     * @param string|mixed $pluginId
     * @param mixed $pluginDefinition
     */
    public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition)
    {
        return new static(
            $configuration,
            $pluginId,
            $pluginDefinition,
            $container->get('entity_type.manager')
        );
    }

    /**
     * @param string|mixed $pluginId
     * @param mixed $pluginDefinition
     */
    public function __construct(array $configuration, $pluginId, $pluginDefinition, EntityTypeManager $entityTypeManager)
    {
        parent::__construct($configuration, $pluginId, $pluginDefinition);

        $this->entityTypeManager = $entityTypeManager;
    }

    /** @param array|mixed $data */
    public function processItem($data): void
    {
        if(isset($data['title']) === false) {
            return;
        }

        $this->entityTypeManager->getStorage('node')->create([
            'title' => $data['title'],
            'type' => 'page',
        ])->save();
    }
}
