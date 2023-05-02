<?php

namespace Drupal\Tests\test_support\Traits\Support\UpdateHook\Contracts;

interface HookHandler
{
    public static function canHandle(string $function): bool;

    public static function create(string $function): self;

    /** @return string[] */
    public static function requiredModuleFiles(): array;

    public function getModuleName(): string;

    public function run(): self;
}
