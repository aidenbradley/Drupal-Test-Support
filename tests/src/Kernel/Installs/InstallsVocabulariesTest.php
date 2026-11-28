<?php

namespace Drupal\Tests\test_support\Kernel\Installs;

use Drupal\KernelTests\KernelTestBase;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\Tests\test_support\Traits\Installs\InstallsVocabularies;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Test;

#[RunTestsInSeparateProcesses]
class InstallsVocabulariesTest extends KernelTestBase
{
    use InstallsVocabularies;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setConfigDirectory(__DIR__ . '/__fixtures__/config/sync/taxonomy');
    }

    /** @test */
    #[Test]
    public function installing_vocabulary_sets_up_dependencies(): void
    {
        $this->assertFalse($this->container->get('module_handler')->moduleExists('taxonomy'));

        $entityTypeDefinitions = $this->container->get('entity_type.manager')->getDefinitions();
        $this->assertArrayNotHasKey('taxonomy_vocabulary', $entityTypeDefinitions);

        $this->installVocabularies('tags');

        $entityTypeDefinitions = $this->container->get('entity_type.manager')->getDefinitions();
        $this->assertArrayHasKey('taxonomy_vocabulary', $entityTypeDefinitions);

        // Drupal 11.2+'s enableModules() rebuilds $this->container at the
        // end (via $this->container->get('kernel')->getContainer()), which
        // produces a fresh module_handler instance - a reference fetched
        // before installVocabularies() (which calls enableModules()
        // internally) would go stale, still reporting pre-install state.
        // Fetch fresh afterwards instead of caching it beforehand.
        $this->assertTrue($this->container->get('module_handler')->moduleExists('taxonomy'));
        $this->assertInstanceOf(Vocabulary::class, $this->container->get('entity_type.manager')->getStorage('taxonomy_vocabulary')->load('tags'));
    }

    /** @test */
    #[Test]
    public function install_single_vocabulary(): void
    {
        $this->enableModules([
            'taxonomy',
        ]);

        $this->installEntitySchema('taxonomy_vocabulary');

        $vocabularyStorage = $this->container->get('entity_type.manager')->getStorage('taxonomy_vocabulary');

        $this->assertEmpty($vocabularyStorage->loadMultiple());

        $this->installVocabularies('tags');

        $vocabularyStorage = $this->container->get('entity_type.manager')->getStorage('taxonomy_vocabulary');

        $this->assertNotEmpty($vocabularyStorage->loadMultiple());

        $this->assertInstanceOf(Vocabulary::class, $vocabularyStorage->load('tags'));
    }

    /** @test */
    #[Test]
    public function install_multiple_vocabularies(): void
    {
        $this->enableModules([
            'taxonomy',
        ]);

        $this->installEntitySchema('taxonomy_vocabulary');

        $vocabularyStorage = $this->container->get('entity_type.manager')->getStorage('taxonomy_vocabulary');

        $this->assertEmpty($vocabularyStorage->loadMultiple());

        $this->installVocabularies([
            'tags',
            'category',
        ]);

        $vocabularyStorage = $this->container->get('entity_type.manager')->getStorage('taxonomy_vocabulary');

        $this->assertNotEmpty($vocabularyStorage->loadMultiple());

        $this->assertInstanceOf(Vocabulary::class, $vocabularyStorage->load('tags'));
        $this->assertInstanceOf(Vocabulary::class, $vocabularyStorage->load('category'));
    }
}
