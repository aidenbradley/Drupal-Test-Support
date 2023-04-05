<?php

namespace Drupal\Tests\test_support\Traits\Installs;

use Drupal\Tests\test_support\Traits\Installs\Configuration\InstallConfiguration;

trait InstallsVocabularies
{
    use InstallConfiguration;

    /** @var bool */
    private $setupVocabularyDependencies = false;

    /** @param  string|array  $vocabularies */
    public function installVocabularies($vocabularies): self
    {
        $this->setupVocabularyDependencies();

        foreach ((array) $vocabularies as $vocabulary) {
            $this->installExportedConfig([
                'taxonomy.vocabulary.' . $vocabulary,
            ]);
        }

        return $this;
    }

    private function setupVocabularyDependencies(): self
    {
        if ($this->setupVocabularyDependencies === false) {
            $this->enableModules([
                'taxonomy',
            ]);

            $this->installEntitySchema('taxonomy_vocabulary');

            $this->setupVocabularyDependencies = true;
        }

        return $this;
    }
}
