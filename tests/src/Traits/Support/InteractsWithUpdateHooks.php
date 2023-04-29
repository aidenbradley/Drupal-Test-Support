<?php

namespace Drupal\Tests\test_support\Traits\Support;

use Drupal\Tests\test_support\Traits\Support\UpdateHook\Factory\HookHandlerFactory;

trait InteractsWithUpdateHooks
{
    public function runUpdateHook(string $function): self
    {
        return $this->handleHook($function);
    }

    public function runPostUpdateHook(string $function): self
    {
        return $this->handleHook($function);
    }

    public function runDeployHook(string $function): self
    {
        return $this->handleHook($function);
    }

    private function handleHook(string $function): self
    {
        $handler = HookHandlerFactory::create($function);

        $this->enableModule($handler->getModuleName());

        foreach ($handler->requiredModuleFiles() as $moduleFile) {
            $this->container->get('module_handler')->loadInclude($handler->getModuleName(), $moduleFile);
        }

        $handler->run();

        return $this;
    }

    private function enableModule(string $module): self
    {
        if ($this->container->get('module_handler')->moduleExists($module) === false) {
            $this->enableModules([
                $module,
            ]);
        }

        return $this;
    }
}
