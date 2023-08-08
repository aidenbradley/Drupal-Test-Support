<?php

namespace Drupal\Tests\test_support\Traits\Support;

use Drupal\Core\Site\Settings;
use PHPUnit\Framework\Assert;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

trait InteractsWithSettings
{
    /** @var bool */
    public $autoDiscoverSettings = false;

    /** @var null|string */
    private $settingsLocationOverride = null;

    /** @var string */
    private $site = 'default';

    /** @var Settings|null */
    private $settings = null;

    public function getConfigurationDirectory(): string
    {
        $directory = $this->getSettings()->get('config_sync_directory');

        if (is_string($directory) === false) {
            Assert::fail('Could not resolve configuration directory');
        }

        return $this->appRoot() . '/' . ltrim($directory, '/');
    }

    protected function getSettings(): Settings
    {
        if ($this->settings instanceof Settings === false) {
            /** @var Settings $settings */
            $settings = $this->temporarilySupressErrors(function () {
                return $this->loadSettings();
            });

            $this->settings = $settings;
        }

        return $this->settings;
    }

    protected function setSettingsLocation(string $settingsLocation): self {
        $this->settingsLocationOverride = $settingsLocation;

        return $this;
    }

    protected function setSite(string $site): self {
        $this->site = $site;

        return $this;
    }

    protected function getSettingsLocation(): string {
        $location = '/sites/' . $this->site . '/settings.php';

        if ($this->settingsLocationOverride !== null) {
            $location = $this->settingsLocationOverride;
        }

        return $this->appRoot() . '/' . ltrim($location, '/');
    }

    private function loadSettings(): Settings
    {
        if ($this->autoDiscoverSettings) {
            return new Settings($this->loadSettingsFromFinder());
        }

        return new Settings($this->loadSettingsFromSitesDirectory());
    }

    /** @return mixed[] */
    private function loadSettingsFromSitesDirectory(): array
    {
        $settings = [];

        if (file_exists($this->getSettingsLocation())) {
            require $this->getSettingsLocation();
        }

        return $settings;
    }

    /** @return mixed[] */
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
        /** @phpstan-ignore-next-line */
        if (version_compare(\Drupal::VERSION, '10.0', '>=')) {
            /** @phpstan-ignore-next-line */
            return $this->container->getParameter('app.root');
        }

        /** @phpstan-ignore-next-line */
        return $this->container->get('app.root');
    }
}
