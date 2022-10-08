<?php

namespace Drupal\Tests\test_support\Traits\Support;

use Drupal\Tests\test_support\Traits\Support\UpdateHook\UpdateHookHandler;
use ReflectionFunction;
use Symfony\Component\Finder\Finder;

trait InteractsWithUpdateHooks
{
    /** @var array */
    private $moduleLocations = [];

    public function runUpdateHook(string $module, string $function): self
    {
        $this->loadModuleFile($module . '.module');

        UpdateHookHandler::handle($function);

        return $this;
    }

    public function runPostUpdateHook(string $module, string $function)
    {
        $this->loadModuleFile($module . '.post_update.php');

        UpdateHookHandler::handle($function);

        return $this;
    }

    private function loadModuleFile(string $moduleFile): string
    {
        if (isset($this->moduleLocations[$moduleFile]) === false) {
            $finder = Finder::create()
                ->ignoreUnreadableDirs()
                ->ignoreDotFiles(true)
                ->name($moduleFile)
                ->in($this->appRoot());

            if ($finder->count() === 1) {
                foreach ($finder as $directory) {
                    require $directory->getPathname();

                    $this->moduleLocations[$moduleFile] = $directory->getPathname();
                }
            }
        }

        return $this->moduleLocations[$moduleFile];
    }

    private function appRoot(): string
    {
        return $this->container->get('app.root');
    }
}
