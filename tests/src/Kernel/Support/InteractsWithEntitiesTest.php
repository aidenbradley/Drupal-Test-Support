<?php

namespace Drupal\Tests\test_support\Kernel\Support;

use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\node\NodeInterface;
use Drupal\Tests\test_support\Traits\Support\InteractsWithEntities;

class InteractsWithEntitiesTest extends KernelTestBase
{
    use InteractsWithEntities;

    protected static $modules = [
        'system',
        'node',
        'user',
    ];

    protected function setUp()
    {
        parent::setUp();

        $this->installEntitySchema('node');
        $this->installEntitySchema('user');

        $this->installSchema('node', 'node_access');

        NodeType::create([
            'type' => 'page',
            'name' => 'Basic page',
        ])->save();
    }

    /** @test */
    public function create_entity(): void
    {
        $node = $this->createEntity('node', [
            'title' => 'Example node',
            'type' => 'page',
        ]);

        $this->assertInstanceOf(NodeInterface::class, $node);
        $this->assertEquals('Example node', $node->title->value);
    }

    /** @test */
    public function update_entity(): void
    {
        $node = $this->createEntity('node', [
            'title' => 'Example node',
            'type' => 'page',
        ]);

        $this->assertInstanceOf(NodeInterface::class, $node);
        $this->assertEquals('Example node', $node->title->value);

        $this->updateEntity($node, [
            'title' => 'Updated Example Title',
        ]);

        $this->assertInstanceOf(NodeInterface::class, $node);
        $this->assertEquals('Updated Example Title', $node->title->value);
    }

    /** @test */
    public function refresh_entity(): void
    {
        $node = $this->createEntity('node', [
            'nid' => 1000,
            'title' => 'Example Title',
            'type' => 'page',
        ]);

        $this->assertEquals('Example Title', $node->title->value);

        $this->updateNodeTitle();

        $this->refreshEntity($node);

        $this->assertEquals('Example Title Updated', $node->title->value);
    }

    private function updateNodeTitle(): void
    {
        $this->storage('node')
            ->load(1000)
            ->set('title', 'Example Title Updated')
            ->save();
    }
}
