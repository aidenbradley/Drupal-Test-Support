<?php

namespace Drupal\Tests\test_support\Kernel\Installs;

use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\test_support\Traits\Installs\InstallsEntityTypes;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

#[RunTestsInSeparateProcesses]
class InstallsEntityTypesTest extends KernelTestBase
{
    use InstallsEntityTypes;

    /** @var string[] */
    protected static $modules = [
        'system',
        'node',
        'user',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->installEntitySchema('user');

        $this->setConfigDirectory(__DIR__ . '/__fixtures__/config/sync/node/bundles');
    }

    /** @test */
    #[Test]
    public function install_bundle(): void
    {
        $this->installEntitySchema('node');

        $nodeTypeStorage = $this->container->get('entity_type.manager')->getStorage('node_type');

        $this->assertEmpty($nodeTypeStorage->loadMultiple());

        $this->installBundle('node', 'page');

        /** @var array<mixed> $nodeTypes */
        $nodeTypes = $nodeTypeStorage->loadMultiple();

        $this->assertNotEmpty($nodeTypes);

        $this->assertInstanceOf(NodeType::class, $nodeTypeStorage->load('page'));
    }

    /** @test */
    #[Test]
    public function install_bundles(): void
    {
        $this->installEntitySchema('node');

        $nodeTypeStorage = $this->container->get('entity_type.manager')->getStorage('node_type');

        $this->assertEmpty($nodeTypeStorage->loadMultiple());

        $this->installBundles('node', [
            'page',
            'news',
        ]);

        $this->assertInstanceOf(NodeType::class, $nodeTypeStorage->load('page'));
        $this->assertInstanceOf(NodeType::class, $nodeTypeStorage->load('news'));
    }

    /** @test */
    #[Test]
    public function install_entity_schema_with_bundles(): void
    {
        $entityTypeManager = $this->container->get('entity_type.manager');

        /** @var \Drupal\Core\Entity\EntityTypeInterface $nodeEntityTypeDefinition */
        $nodeEntityTypeDefinition = $entityTypeManager->getDefinition('node');

        // getDataTable() is nullable in general (config entities and
        // others have no data table at all), but node is a content entity
        // type with SQL storage, so it's always a string here.
        /** @var string $nodeDataTable */
        $nodeDataTable = $nodeEntityTypeDefinition->getDataTable();

        $this->assertFalse($this->container->get('database')->schema()->tableExists(
            $nodeDataTable
        ));

        $nodeTypeStorage = $entityTypeManager->getStorage('node_type');

        $this->assertEmpty($nodeTypeStorage->loadMultiple());

        $this->installEntitySchemaWithBundles('node', [
            'page',
            'news',
        ]);

        $this->assertTrue($this->container->get('database')->schema()->tableExists(
            $nodeDataTable
        ));

        $this->assertInstanceOf(NodeType::class, $nodeTypeStorage->load('page'));
        $this->assertInstanceOf(NodeType::class, $nodeTypeStorage->load('news'));
    }
}
