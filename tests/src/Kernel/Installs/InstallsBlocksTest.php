<?php

namespace Drupal\Tests\test_support\Kernel\Installs;

use Drupal\block\Entity\Block;
use Drupal\Core\Config\Entity\ConfigEntityType;
use Drupal\KernelTests\KernelTestBase;
use AidenBradley\DrupalTestSupport\Installs\InstallsBlocks;

class InstallsBlocksTest extends KernelTestBase
{
    use InstallsBlocks;

    /** @var string[] */
    protected static $modules = [
        'system',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->setConfigDirectory(__DIR__ . '/__fixtures__/config/sync/blocks');
    }

    /** @test */
    public function installing_block_prepares_dependencies(): void
    {
        $moduleHandler = $this->container->get('module_handler');

        $this->assertFalse($moduleHandler->moduleExists('block'));

        $entityTypeDefinitions = $this->container->get('entity_type.manager')->getDefinitions();
        $this->assertArrayNotHasKey('block', $entityTypeDefinitions);

        $this->installBlocks('stark_messages');

        $this->assertTrue($moduleHandler->moduleExists('block'));
        $this->assertInstanceOf(ConfigEntityType::class, $this->container->get('entity_type.manager')->getDefinition('block'));
    }

    /** @test */
    public function install_single_block(): void
    {
        $this->enableModules([
            'block',
        ]);
        $this->installEntitySchema('block');

        $blockStorage = $this->container->get('entity_type.manager')->getStorage('block');

        $this->assertEmpty($blockStorage->loadMultiple());

        $this->installBlocks('stark_messages');

        /** @var array<mixed> $blocks */
        $blocks = $blockStorage->loadMultiple();

        $this->assertNotEmpty($blocks);

        $this->assertInstanceOf(Block::class, $blockStorage->load('stark_messages'));
    }

    /** @test */
    public function install_multiple_blocks(): void
    {
        $this->enableModules([
            'block',
        ]);
        $this->installEntitySchema('block');

        $blockStorage = $this->container->get('entity_type.manager')->getStorage('block');

        $this->assertEmpty($blockStorage->loadMultiple());

        $this->installBlocks([
            'stark_messages',
            'stark_second_block',
        ]);

        $this->assertInstanceOf(Block::class, $blockStorage->load('stark_messages'));
        $this->assertInstanceOf(Block::class, $blockStorage->load('stark_second_block'));
    }
}
