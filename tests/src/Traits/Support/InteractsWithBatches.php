<?php

namespace Drupal\Tests\drupal_test_support\Traits\Support;

trait InteractsWithBatches
{
    public function runLatestBatch(string $redirect = '/'): self
    {
        $batch =& batch_get();

        $batch['progressive'] = false;

        batch_process($redirect);

        return $this;
    }
}