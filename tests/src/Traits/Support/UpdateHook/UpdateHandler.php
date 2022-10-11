<?php

namespace Drupal\Tests\test_support\Traits\Support\UpdateHook;

use Drupal\Tests\test_support\Traits\Support\UpdateHook\Base\UpdateHookHandler;

class UpdateHandler extends UpdateHookHandler
{
    public function getModuleName(): string
    {
        $matches = [];

        preg_match_all('(_update_\d{4})', $this->function, $matches);

        return explode($matches[0][0], $this->function)[0];
    }
}
