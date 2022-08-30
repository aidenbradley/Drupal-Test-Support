<?php

namespace Drupal\Tests\test_traits\Traits;

use Drupal\Core\Site\Settings;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

trait InteractsWithSettings
{
    /** @var bool */
    public $autoDiscoverSettings = false;

    /** @var string */
    private $settingsLocation = '/sites/default/settings.php';

    /** @var Settings|null  */
    private $settings = null;

    public function getConfigurationDirectory(): string
    {
        $directory = $this->getSettings()->get('config_sync_directory');

        return $this->appRoot() . '/' . ltrim($directory, '/');
    }

    protected function getSettings(): Settings
    {
        if (isset($this->settings) === false) {
            $this->temporarilySupressErrors(function() {
                $this->loadSettings();
            });
        }

        return $this->settings;
    }

    private function loadSettings(): void
    {
        if ($this->autoDiscoverSettings) {
            $this->settings = new Settings($this->loadSettingsFromFinder());

            return;
        }

        $this->settings = new Settings($this->loadSettingsFromSitesDirectory());
    }

    private function loadSettingsFromSitesDirectory(): array
    {
        $settings = [];

        require $this->appRoot() . '/' . ltrim($this->settingsLocation, '/');

        return $settings;
    }

    private function loadSettingsFromFinder(): array
    {
        $settings = [];

        $finder = Finder::create()
            ->ignoreUnreadableDirs()
            ->ignoreDotFiles(true)
            ->name('settings.php')
            ->filter(function(SplFileInfo $file) {
                return str_contains($file->getPathname(), 'simpletest') === false;
            })
            ->in($this->appRoot());

        foreach ($finder as $directory) {
            require $directory->getPathname();
        }

        return $settings;
    }

    /** @return mixed */
    private function temporarilySupressErrors(callable $callback)
    {
        $currentErrorReportingLevel = error_reporting();

        error_reporting(E_ALL & ~E_NOTICE);

        $result = $callback();

        error_reporting($currentErrorReportingLevel);

        return $result;
    }

    private function appRoot(): string
    {
        return $this->container->get('app.root');
    }
}
