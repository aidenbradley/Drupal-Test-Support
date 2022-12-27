<?php

namespace Drupal\Tests\test_support\Traits\Support;

use Drupal\Tests\test_support\Traits\Support\UpdateHook\DeployHookHandler;
use Drupal\Tests\test_support\Traits\Support\UpdateHook\PostUpdateHandler;
use Drupal\Tests\test_support\Traits\Support\UpdateHook\UpdateHandler;

trait InteractsWithUpdateHooks
{
    public function runUpdateHook(string $function): self
    {
        $handler = UpdateHandler::create($function);

        $this->enableModule($handler->getModuleName());

        $this->container->get('module_handler')->loadInclude($handler->getModuleName(), 'install');

        $handler->run();

        return $this;
    }

    public function runPostUpdateHook(string $function)
    {
        $handler = PostUpdateHandler::create($function);

        $this->enableModule($handler->getModuleName());

        $this->container->get('module_handler')->loadInclude($handler->getModuleName(), 'post_update.php');

        $handler->run();

        return $this;
    }

    public function runDeployHook(string $function)
    {
        $handler = DeployHookHandler::create($function);

        $this->enableModule($handler->getModuleName());

        $this->container->get('module_handler')->loadInclude($handler->getModuleName(), 'install');
        $this->container->get('module_handler')->loadInclude($handler->getModuleName(), 'deploy.php');

        $handler->run();

        return $this;
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
