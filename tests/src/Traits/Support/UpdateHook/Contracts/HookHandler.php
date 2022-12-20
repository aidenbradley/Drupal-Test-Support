<?php

namespace Drupal\Tests\test_support\Traits\Support\UpdateHook\Contracts;

interface HookHandler
{
    public static function canHandle(string $function): bool;

    public static function create(string $function);

    public function run();
}
