<?php

namespace Drupal\Tests\test_support\Traits\Support;

use Drupal\Core\Site\Settings;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

trait InteractsWithSettings
{
    /** @var bool */
    public $autoDiscoverSettings = false;

    /** @var string */
    private $settingsLocation = '/sites/default/settings.php';

    /** @var Settings|null */
    private $settings = null;

    public function getConfigurationDirectory(): string
    {
        $directory = $this->getSettings()->get('config_sync_directory');

        return $this->appRoot() . '/' . ltrim($directory, '/');
    }

    protected function getSettings(): Settings
    {
        if ($this->settings instanceof Settings === false) {
            $this->settings = $this->temporarilySupressErrors(function () {
                return $this->loadSettings();
            });
        }

        return $this->settings;
    }

    private function loadSettings(): Settings
    {
        if ($this->autoDiscoverSettings) {
            return new Settings($this->loadSettingsFromFinder());
        }

        return new Settings($this->loadSettingsFromSitesDirectory());
    }

    private function loadSettingsFromSitesDirectory(): array
    {
        $settings = [];

        $settingsFileLocation = $this->appRoot() . '/' . ltrim($this->settingsLocation, '/');

        if (file_exists($settingsFileLocation)) {
            require $settingsFileLocation;
        }

        return $settings;
    }

    private function loadSettingsFromFinder(): array
    {
        $settings = [];

        $finder = Finder::create()
            ->ignoreUnreadableDirs()
            ->ignoreDotFiles(true)
            ->name('settings.php')
            ->filter(function (SplFileInfo $file) {
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

        error_reporting(0);

        $result = $callback();

        error_reporting($currentErrorReportingLevel);

        return $result;
    }

    private function appRoot(): string
    {
        if (str_starts_with(\Drupal::VERSION, '10.')) {
            /** @phpstan-ignore-next-line */
            return $this->container->getParameter('app.root');
        }

        /** @phpstan-ignore-next-line */
        return $this->container->get('app.root');
    }
}
