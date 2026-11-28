<?php

namespace Drupal\Tests\test_support\Kernel\Installs;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\test_support\Traits\Installs\InstallsViews;
use Drupal\views\Entity\View;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

#[RunTestsInSeparateProcesses]
class InstallsViewsTest extends KernelTestBase
{
    use InstallsViews;

    protected function setUp(): void
    {
        // Drupal 12's own KernelTestBase declares this property as typed
        // (bool), and PHP disallows a subclass redeclaring an inherited
        // typed property with a different (or no) type - but PHP 7.3,
        // this module's own floor, has no typed properties at all, so
        // redeclaring it here can't satisfy both. Assigning to the
        // inherited property instead needs no redeclaration, and works
        // identically either way; it has to happen before parent::setUp()
        // runs, since that's what reads it.
        $this->strictConfigSchema = false;

        parent::setUp();

        $this->setConfigDirectory(__DIR__ . '/__fixtures__/config/sync/views');
    }

    /** @test */
    #[Test]
    public function installing_view_sets_up_dependencies(): void
    {
        $this->assertFalse($this->container->get('module_handler')->moduleExists('views'));

        $entityTypeDefinitions = $this->container->get('entity_type.manager')->getDefinitions();
        $this->assertArrayNotHasKey('view', $entityTypeDefinitions);

        $this->installViews('media');

        $entityTypeDefinitions = $this->container->get('entity_type.manager')->getDefinitions();
        $this->assertArrayHasKey('view', $entityTypeDefinitions);

        $this->assertTrue($this->container->get('module_handler')->moduleExists('views'));
    }

    /** @test */
    #[Test]
    public function install_single_view(): void
    {
        $this->enableModules([
            'system',
            'user',
            'views',
        ]);
        $this->installEntitySchema('view');

        $viewStorage = $this->container->get('entity_type.manager')->getStorage('view');

        $this->assertEmpty($viewStorage->loadMultiple());

        $this->installViews('media');

        /** @var array<mixed> $views */
        $views = $viewStorage->loadMultiple();

        $this->assertNotEmpty($views);

        $this->assertInstanceOf(View::class, $viewStorage->load('media'));
    }

    /** @test */
    #[Test]
    public function install_multiple_views(): void
    {
        $this->enableModules([
            'system',
            'user',
            'views',
        ]);
        $this->installEntitySchema('view');

        $viewStorage = $this->container->get('entity_type.manager')->getStorage('view');

        $this->assertEmpty($viewStorage->loadMultiple());

        $this->installViews([
            'media',
            'redirect',
        ]);

        $this->assertInstanceOf(View::class, $viewStorage->load('media'));
        $this->assertInstanceOf(View::class, $viewStorage->load('redirect'));
    }
}
