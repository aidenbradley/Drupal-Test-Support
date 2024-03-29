<?php

namespace Drupal\Tests\test_support\Kernel\Installs;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\test_support\Traits\Installs\InstallsRoles;
use Drupal\user\Entity\Role;

class InstallsRolesTest extends KernelTestBase
{
    use InstallsRoles;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setConfigDirectory(__DIR__ . '/__fixtures__/config/sync/roles');
    }

    /** @test */
    public function installing_role_sets_dependencies(): void
    {
        $this->assertFalse($this->container->get('module_handler')->moduleExists('user'));

        $entityTypeDefinitions = $this->container->get('entity_type.manager')->getDefinitions();
        $this->assertArrayNotHasKey('user_role', $entityTypeDefinitions);

        $this->installRoles('editor');

        $this->assertTrue($this->container->get('module_handler')->moduleExists('user'));

        $entityTypeDefinitions = $this->container->get('entity_type.manager')->getDefinitions();
        $this->assertArrayHasKey('user_role', $entityTypeDefinitions);
    }

    /** @test */
    public function install_single_role(): void
    {
        $this->enableModules([
            'system',
            'user',
        ]);
        $this->installEntitySchema('user_role');

        $roleStorage = $this->container->get('entity_type.manager')->getStorage('user_role');

        $this->assertEmpty($roleStorage->loadMultiple());

        $this->installRoles('editor');

        /** @var \Drupal\user\RoleInterface[] $roles */
        $roles = $roleStorage->loadMultiple();

        $this->assertNotEmpty($roles);

        $editor = reset($roles);

        $this->assertEquals('editor', $editor->id());
    }

    /** @test */
    public function install_multiple_roles(): void
    {
        $this->enableModules([
            'system',
            'user',
        ]);
        $this->installEntitySchema('user_role');

        $roleStorage = $this->container->get('entity_type.manager')->getStorage('user_role');

        $this->assertEmpty($roleStorage->loadMultiple());

        $this->installRoles([
            'editor',
            'writer',
        ]);

        $this->assertInstanceOf(Role::class, $roleStorage->load('editor'));
        $this->assertInstanceOf(Role::class, $roleStorage->load('writer'));
    }
}
