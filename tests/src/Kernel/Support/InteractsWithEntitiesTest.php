<?php

namespace Drupal\Tests\test_support\Kernel\Support;

use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\node\NodeInterface;
use Drupal\Tests\test_support\Traits\Support\InteractsWithEntities;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Test;

#[RunTestsInSeparateProcesses]
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
    #[Test]
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
    #[Test]
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
    #[Test]
    public function refresh_entity(): void
    {
        /** @var \Drupal\node\Entity\Node $node */
        $node = $this->createEntity('node', [
            'nid' => 1000,
            'title' => 'Example Title',
            'type' => 'page',
        ]);

        $this->assertEquals('Example Title', $node->get('title')->getString());

        // The node was just created with an explicit 'nid' above, so
        // id() is never null here - narrower than its own general,
        // nullable (unsaved-entity) return type.
        /** @var int|string $nodeId */
        $nodeId = $node->id();

        $this->updateNodeTitle($nodeId);

        $this->refreshEntity($node);

        /** @var \Drupal\node\Entity\Node $node */
        $this->assertEquals('Example Title Updated', $node->get('title')->getString());
    }

    // Always called with an already-saved node's own id(), which is
    // never null - narrower than EntityInterface::id()'s own general
    // (nullable, for unsaved entities) return type.
    /** @param int|string $nodeId */
    private function updateNodeTitle($nodeId): void
    {
        $node = $this->storage('node')->load($nodeId);

        if ($node instanceof Node === false) {
            $this->fail('Could not load node with ID: ' . $nodeId);
        }

        $node->set('title', 'Example Title Updated')->save();
    }
}
