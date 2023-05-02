<?php

namespace AidenBradley\DrupalTestSupport\Support\Exceptions;

class ConfigInstallFailed extends \Exception
{
    public const CONFIGURATION_DOES_NOT_EXIST = 1;

    public const FAILED_HANDLING_CONFIGURATION = 2;

    /** @var string */
    private $failingConfigFile = '';

    public static function doesNotExist(string $configFile): self
    {
        $exception = new self(
            'Configuration file ' . $configFile . ' does not exist',
            self::CONFIGURATION_DOES_NOT_EXIST
        );

        return $exception->setFailingConfigFile($configFile);
    }

    public static function couldNotHandle(string $configFile): self
    {
        $exception = new self(
            'The following configuration has failed to import ' . $configFile,
            self::FAILED_HANDLING_CONFIGURATION
        );

        return $exception->setFailingConfigFile($configFile);
    }

    public function getFailingConfigFile(): string
    {
        return $this->failingConfigFile;
    }

    private function setFailingConfigFile(string $configFile): self
    {
        $this->failingConfigFile = $configFile;

        return $this;
    }
}
