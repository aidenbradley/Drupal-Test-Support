<?php

namespace Drupal\Tests\test_support\Kernel\Support;

use Drupal\Core\Url;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\test_support\Traits\Http\MakesHttpRequests;
use Drupal\Tests\test_support\Traits\Support\InteractsWithBatches;
use Drupal\user\UserInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

#[RunTestsInSeparateProcesses]
class InteractsWithBatchesTest extends KernelTestBase
{
    use InteractsWithBatches;
    use MakesHttpRequests;

    /** @var string[] */
    protected static $modules = [
        'system',
        'user',
        'test_support_batch',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->installEntitySchema('user');
        // Drupal 12 removed the sequences table (and its underlying
        // \Drupal::service('database')->nextId() table-based mechanism)
        // entirely in favour of native database sequences - it's both
        // invalid and unnecessary to install there.
        if (version_compare(\Drupal::VERSION, '12.0', '<')) {
            $this->installSchema('system', 'sequences');
        }
    }

    /** @test */
    #[Test]
    public function run_batch_thats_been_set(): void
    {
        $this->createEnabledUser('enabled_user_one')
            ->createEnabledUser('enabled_user_two')
            ->createEnabledUser('enabled_user_three');

        $this->get($this->route('disable_all_users.prepare_batch'));

        $this->runLatestBatch();

        $this->assertEquals(0, $this->loadUser(1)->get('status')->getString());
        $this->assertEquals(0, $this->loadUser(2)->get('status')->getString());
        $this->assertEquals(0, $this->loadUser(3)->get('status')->getString());
    }

    /** @test */
    #[Test]
    public function run_batch_thats_been_processed(): void
    {
        $this->createEnabledUser('enabled_user_one')
            ->createEnabledUser('enabled_user_two')
            ->createEnabledUser('enabled_user_three');

        $this->assertEquals('1', $this->loadUser(1)->get('status')->getString());
        $this->assertEquals('1', $this->loadUser(2)->get('status')->getString());
        $this->assertEquals('1', $this->loadUser(3)->get('status')->getString());

        $this->get($this->route('disable_all_users.prepare_and_process_batch'));

        $this->runLatestBatch();

        $this->assertEquals('0', $this->loadUser(1)->get('status')->getString());
        $this->assertEquals('0', $this->loadUser(2)->get('status')->getString());
        $this->assertEquals('0', $this->loadUser(3)->get('status')->getString());
    }

    private function createEnabledUser(string $name): self
    {
        $this->container->get('entity_type.manager')->getStorage('user')->create([
            'status' => 1,
            'mail' => $name . '@example.com',
            'name' => $name,
        ])->save();

        return $this;
    }

    /**
     * @param array<mixed> $parameters
     * @param array<mixed> $options
     */
    private function route(string $route, array $parameters = [], array $options = []): string
    {
        return Url::fromRoute($route, $parameters, $options)->toString(true)->getGeneratedUrl();
    }

    /** @return UserInterface<mixed> */
    private function loadUser(int $userId): UserInterface
    {
        $user = $this->container->get('entity_type.manager')->getStorage('user')->load($userId);

        if ($user instanceof UserInterface === false) {
            $this->fail('Could not load user ID: ' . $userId);
        }

        return $user;
    }
}
