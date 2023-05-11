<?php

namespace Drupal\Tests\test_support\Traits\Support\Exceptions;

use Throwable;

class ConfigInstallFailed extends \Exception
{
    public const CONFIGURATION_DOES_NOT_EXIST = 1;

    public const FAILED_HANDLING_CONFIGURATION = 2;

    /** @var string */
    private $failingConfigFile;

    public function __construct(string $failingConfigFile, string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->failingConfigFile = $failingConfigFile;
    }

    public static function doesNotExist(string $configFile): self
    {
        return new self(
            $configFile,
            'Configuration file ' . $configFile . ' does not exist',
            self::CONFIGURATION_DOES_NOT_EXIST
        );
    }

    public function getFailingConfigFile(): string
    {
        return $this->failingConfigFile;
    }
}
