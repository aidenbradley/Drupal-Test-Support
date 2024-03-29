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

    /** @var string[] */
    protected static $modules = [
        'system',
        'node',
        'user',
    ];

    protected function setUp(): void
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
        /** @var \Drupal\node\Entity\Node $node */
        $node = $this->createEntity('node', [
            'nid' => 1000,
            'title' => 'Example Title',
            'type' => 'page',
        ]);

        $this->assertEquals('Example Title', $node->get('title')->getString());

        $this->updateNodeTitle($node->id());

        $this->refreshEntity($node);

        $this->assertEquals('Example Title Updated', $node->get('title')->getString());
    }

    /** @param int|string|null $nodeId */
    private function updateNodeTitle($nodeId): void
    {
        $node = $this->storage('node')->load($nodeId);

        if ($node instanceof Node === false) {
            $this->fail('Could not load node with ID: ' . $nodeId);
        }

        $node->set('title', 'Example Title Updated')->save();
    }
}
