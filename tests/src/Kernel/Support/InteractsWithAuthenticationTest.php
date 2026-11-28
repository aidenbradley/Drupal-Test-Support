<?php

namespace Drupal\Tests\test_support\Kernel\Support;

use Drupal\Core\Url;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\test_support\Traits\Http\MakesHttpRequests;
use Drupal\Tests\test_support\Traits\Support\InteractsWithAuthentication;
use Drupal\user\Entity\Role;
use Drupal\user\UserInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

#[RunTestsInSeparateProcesses]
class InteractsWithAuthenticationTest extends KernelTestBase
{
    use InteractsWithAuthentication;
    use MakesHttpRequests;

    /** @var string[] */
    protected static $modules = [
        'system',
        'user',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->installEntitySchema('user');
    }

    /** @test */
    #[Test]
    public function acting_as(): void
    {
        $userStorage = $this->container->get('entity_type.manager')->getStorage('user');

        $userStorage->create([
            'uid' => 1,
            'name' => 'authenticated_user',
            'status' => 1,
        ])->save();

        $user = $userStorage->load(1);

        if ($user instanceof UserInterface === false) {
            $this->fail('Could not load user with ID: 1');
        }

        $this->actingAs($user)->get(
            $this->route('entity.user.canonical', [
                'user' => $user->id(),
            ])
        )->assertOk();

        $this->assertEquals($user->id(), $this->container->get('current_user')->getAccount()->id());

        $this->actingAsAnonymous()->get(
            $this->route('entity.user.canonical', [
                'user' => 0,
            ])
        )->assertForbidden();
    }

    /** @test */
    #[Test]
    public function acting_as_role(): void
    {
        $this->enableModules([
            'system',
            'user',
        ]);
        // Drupal 12 removed the sequences table (and its underlying
        // \Drupal::service('database')->nextId() table-based mechanism)
        // entirely in favour of native database sequences - it's both
        // invalid and unnecessary to install there.
        if (version_compare(\Drupal::VERSION, '12.0', '<')) {
            $this->installSchema('system', 'sequences');
        }
        $this->installEntitySchema('user_role');

        $adminRole = $this->createRole('administrator');
        $this->actingAsRole($adminRole)->assertTrue(
            in_array($adminRole->id(), $this->container->get('current_user')->getRoles())
        );

        $editorRole = $this->createRole('editor');
        $this->actingAs($editorRole)->assertTrue(
            in_array($editorRole->id(), $this->container->get('current_user')->getRoles())
        );
    }

    /** @test */
    #[Test]
    public function acting_as_roles(): void
    {
        $this->enableModules([
            'system',
            'user',
        ]);
        // Drupal 12 removed the sequences table (and its underlying
        // \Drupal::service('database')->nextId() table-based mechanism)
        // entirely in favour of native database sequences - it's both
        // invalid and unnecessary to install there.
        if (version_compare(\Drupal::VERSION, '12.0', '<')) {
            $this->installSchema('system', 'sequences');
        }
        $this->installEntitySchema('user_role');

        $adminRole = $this->createRole('administrator');
        $editorRole = $this->createRole('editor');

        $this->actingAsRoles([
            $adminRole,
            $editorRole,
        ]);
        $this->assertTrue(
            in_array($adminRole->id(), $this->container->get('current_user')->getRoles())
        );
        $this->assertTrue(
            in_array($editorRole->id(), $this->container->get('current_user')->getRoles())
        );
    }

    private function createRole(string $roleId): Role
    {
        $role = $this->container->get('entity_type.manager')->getStorage('user_role')->create([
            'id' => $roleId,
            'label' => $roleId,
        ]);
        $role->save();

        return $role;
    }

    /**
     * @param array<mixed> $parameters
     * @param array<mixed> $options
     */
    private function route(string $route, array $parameters = [], array $options = []): string
    {
        return Url::fromRoute($route, $parameters, $options)->toString(true)->getGeneratedUrl();
    }
}
