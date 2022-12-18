<?php

namespace Drupal\Tests\test_support\Traits\Support;

use Drupal\Tests\test_support\Traits\Support\UpdateHook\DeployHookHandler;
use Drupal\Tests\test_support\Traits\Support\UpdateHook\PostUpdateHandler;
use Drupal\Tests\test_support\Traits\Support\UpdateHook\UpdateHandler;
use Symfony\Component\Finder\Finder;

trait InteractsWithUpdateHooks
{
    public function runUpdateHook(string $function): self
    {
        $handler = UpdateHandler::create($function);

        $this->enableModule($handler->getModuleName());
        $this->requireFIle($handler->getModuleName() . '.install');

        $handler->run();

        return $this;
    }

    public function runPostUpdateHook(string $function)
    {
        $handler = PostUpdateHandler::create($function);

        $this->enableModule($handler->getModuleName());
        $this->requireFile($handler->getModuleName() . '.post_update.php');

        $handler->run();

        return $this;
    }

    public function runDeployHook(string $function)
    {
        $handler = DeployHookHandler::create($function);

        $this->enableModule($handler->getModuleName());
        $this->requireFile($handler->getModuleName() . '.install');
        $this->requireFile($handler->getModuleName() . '.deploy.php');

        $handler->run();

        return $this;
    }

    private function requireFile(string $moduleFile): void
    {
        $finder = Finder::create()
            ->ignoreUnreadableDirs()
            ->ignoreDotFiles(true)
            ->name($moduleFile)
            ->in($this->appRoot());

        foreach ($finder as $directory) {
            require $directory->getPathname();
        }
    }

    private function enableModule(string $module): self
    {
        if ($this->container->get('module_handler')->moduleExists($module) === false) {
            $this->enableModules([
                $module
            ]);
        }

        return $this;
    }

    private function appRoot(): string
    {
        return $this->container->get('app.root');
    }
}
