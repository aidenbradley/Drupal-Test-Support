[Introduction](#introduction)

[Strict configuration](#strict-configuration)
- [Enabling Strict configuration](#enabling-strict-schema-checks)
- [Disabling Strict configuration](#disabling-strict-schema-checks)

[Installing Configuration Files](#installing-configuration-files)
- [Installing a single configuration file](#installing-a-single-configuration-file)
- [Installing multiple configuration files](#installing-multiple-configuration-files)

[Installing Blocks](#installing-blocks)
- [Installing a single block](#installing-a-single-block)
- [Installing multiple blocks](#installing-multiple-blocks)

[Installing Entity Types and Bundles](#installing-entity-types-and-bundles)
- [Installing a single bundle](#installing-a-single-bundle)

# Introduction
The Installs API resides inside a single trait found at <code>Drupal\Tests\test_support\Traits\Installs\InstallsExportedConfig</code>.

Its purpose is to allow developers to install exported configuration during test runs.

The `InstallsExportedConfig` trait can install just about any exported configuration file, but there are other traits it makes use of to provide a better developer experience when importing certain configuration.

## Strict Configuration
During some test runs, you may run into schema errors when importing configuration. [Although not recommended](https://www.drupal.org/node/2391795), you can disable strict schema checks when importing configuration.

Schema errors can often happen when other dependent configuration files haven't been imported. It may be the case that your test does not explicitly need them. For this reason, we provide two fluent methods to enable or disable strict schema checks.

The methods to enable and disable the strict schema config checks are found inside the [InstallsExportedConfig](./tests/src/Traits/Installs/InstallsExportedConfig.php) trait
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

### Installing configuration files
As mentioned, the Installs API is there to allow developers to install configuration during their test runs. Most of the traits found inside the [traits](./tests/src/Traits/Installs) directory will have a method called `installsExportedConfig`.

To install a configuration file, simply call the `installExportedConfig` method. The method expects the full configuration name <b>without the .yml extension</b>.

#### Installing a single configuration file
```php
public function install_configuration(): void
{
    $this->installExportedConfig('my_configuration.file');
}
```

#### Installing multiple configuration files
```php
public function install_configuration(): void
{
    $this->installExportedConfig([
        'my_configuration.file_1',
        'my_configuration.file_2',
    ]);
}
```

### Installing blocks
There is a specific trait called [InstallsBlocks](./tests/src/Traits/Installs/InstallsBlocks.php) that provides a helpful method called `installBlocks`.

The trait attempts to do two things in the way of developer experience
- It attempts to set up the necessary dependencies required to import the block configuration, such as enabling the `block` module and installing the `block` entity schema.
- It attempts to install the block based on the typical `block.block.BLOCK_NAME` configuration naming convention, meaning you only have to pass the block ID to install the block configuration.

To install a block, simply call the `installBlocks` method.

#### Installing a single block
```php
public function install_single_block(): void
{
    $this->installBlocks('stark_messages');
}
```
#### Installing multiple blocks
```php
public function install_multiple_block(): void
{
    $this->installBlocks([
        'stark_messages',
        'stark_second_block',
    ]);
}
```

### Installing Entity Types and Bundles
There is a specific trait called [InstallsEntityTypes](./tests/src/Traits/Installs/InstallsEntityTypes.php) that provides a few helpful methods to install entity type schemas and bundles of that entity type.

> [!IMPORTANT]
> These methods to <b>not</b> attempt to prepare dependencies, such as installing modules or installing entity schemas. For now, it is expected that these will be set up before installing configuration via the [InstallsEntityTypes](./tests/src/Traits/Installs/InstallsEntityTypes.php) trait.

> [!IMPORTANT]
> The `installBundle` and `installBundles` method expects the bundle entity key to be type. Using these methods to install bundles for media, for example, may not work.
>
> Further work must be carried out to resolve the bundle entity key of the entity type the bundle is being installed for.

#### Installing a single bundle
The example below will install the `page` bundle for the `node` entity type.

The `installBundle` method expects
- The first parameter to be the module that defines the entity type
- The second parameter to be the name of the bundle to install
```php
public function install_page_bundle_for_node(): void
{
    $this->installBundle('node', 'page');
}
```

#### Installing multiple bundles
The example below will install the `page` and `article` bundles for the `node` entity type.

The `installBundles` method expects
- The first parameter to be the module that defines the entity type
- The second parameter to be the name of the bundle to install
```php
public function install_page_and_article_bundles_for_node(): void
{
    $this->installBundle('node', [
        'page',
        'article',
    ]);
}
```
