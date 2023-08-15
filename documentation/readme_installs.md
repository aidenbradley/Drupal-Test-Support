[Introduction](#introduction)

[Strict configuration](#strict-configuration)
- [Enabling Strict configuration](#enabling-strict-schema-checks)
- [Disabling Strict configuration](#disabling-strict-schema-checks)

# Introduction
The Installs API resides inside a single trait found at <code>Drupal\Tests\test_support\Traits\Installs\InstallsExportedConfig</code>.

Its purpose is to allow developers to install exported configuration during test runs.

The `InstallsExportedConfig` trait can install just about any exported configuration file, but there are other traits it makes use of to provide a more convenient developer exprience when importing certain configuration.

## Strict Configuration
During some test runs, you may run into schema errors when importing configuration. Although not recommended, you can disable strict schema checks when importing configuration.

Schema errors can often happen when other dependent configuration files haven't been imported. It may be the case that your test does not explicitly need them. For this reason, we provide two fluent methods to enable or disable strict schema checks.

### Enabling strict schema checks
As mentioned, this is the default during test runs. You must explicity set strict schema checks to false.

In the instance you need to enable it though, call the `enableStrictConfig` method.

```php
public function enable_strict_config(): void
{
    $this->enableStrictConfig()->installExportedConfig('my_config_file');
}
```

### Disabling strict schema checks
To disable strict schema checks, call the `disableStrictConfig` method.

```php
public function disable_strict_config(): void
{
    $this->disableStrictConfig()->installExportedConfig('my_config_file');
}
```
