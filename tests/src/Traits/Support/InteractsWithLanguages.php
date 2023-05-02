<?php

namespace Drupal\Tests\test_support\Traits\Support;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\language\ConfigurableLanguageInterface;
use Drupal\Tests\test_support\Traits\Installs\InstallsExportedConfig;
use PHPUnit\Framework\Assert;

trait InteractsWithLanguages
{
    use InstallsExportedConfig;

    /** @var string[] */
    protected $installedLanguages = [
        'en', // EN is installed by default
    ];

    /** @var bool */
    protected $installLanguageModule = false;

    protected function languageManager(): LanguageManagerInterface
    {
        return $this->container->get('language_manager');
    }

    /** @param string|string[] $langcodes */
    protected function installLanguage($langcodes): void
    {
        $this->setupLanguageDependencies();

        foreach ((array) $langcodes as $langcode) {
            $this->installExportedConfig('language.entity.' . $langcode);
        }
    }

    /** @param ConfigurableLanguageInterface|string $language */
    protected function setCurrentLanguage($language, ?string $prefix = null): void
    {
        $this->setupLanguageDependencies();

        if (is_string($language)) {
            if (in_array($language, $this->installedLanguages) === false) {
                $this->installLanguage($language);
            }

            $language = $this->container->get('entity_type.manager')->getStorage(
                'configurable_language'
            )->load($language);
        }

        if ($language instanceof ConfigurableLanguageInterface === false) {
            Assert::fail('Could not install language');
        }

        $this->config('system.site')
            ->set('langcode', $language->getId())
            ->set('default_langcode', $language->getId())
            ->save();

        if ($prefix !== null) {
            $languageNegotiation = $this->config('language.negotiation');

            $prefixes = $languageNegotiation->get('url.prefixes');

            $prefixes[$language->id()] = $prefix;

            $languageNegotiation->set('url.prefixes', $prefixes)->save();
        }

        $this->container->get('language.default')->set($language);

        /** @phpstan-ignore-next-line */
        $this->container->get('kernel')->rebuildContainer();

        $this->languageManager()->reset();

        $this->installedLanguages[] = $language->getId();
    }

    private function setupLanguageDependencies(): void
    {
        if ($this->installLanguageModule) {
            return;
        }

        $this->enableModules(['language']);
        $this->installConfig('language');
        $this->installEntitySchema('configurable_language');

        $this->installLanguageModule = true;
    }
}
