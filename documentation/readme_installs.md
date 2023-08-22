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

[Installing Entity Types and Bundles at the same time](#installing-entity-type-and-bundles-at-the-same-time)
- [Installing Entity Type with single Bundle](#installing-entity-type-with-single-bundle)
- [Installing Entity Type with multiple Bundles](#installing-entity-type-with-multiple-bundles)

[Installing Fields](#installing-fields)
- [Installing a single field for an entity type](#installing-a-single-field-for-an-entity-type)
- [Installing multiple fields for an entity type](#installing-multiple-fields-for-an-entity-type)
- [Installing a single field for a bundle of an entity type](#installing-a-single-field-for-a-bundle-of-an-entity-type)
- [Installing multiple field for a bundle of an entity type](#installing-multiple-field-for-a-bundle-of-an-entity-type)
- [Installing all fields for an Entity Type](#installing-all-fields-for-an-entity-type)
- [Installing all fields for an Entity Type Bundle](#installing-all-fields-for-an-entity-type-bundle)

[Installing Image Styles](#installing-image-styles)
- [Install a single image style](#installing-a-single-image-style)
- [Install multiple image styles](#installing-multiple-image-styles)

[Installing Menus](#installing-menus)
- [Installing a single menu](#installing-a-single-menu)
- [Installing multple menus](#installing-multiple-menus)

[Installing Modules](#installing-modules)
- [Installing a module and its dependencies (recursively)](#installing-a-module-and-its-dependencies--recursively-)

[Installing Roles](#installing-roles)
- [Installing a single role](#installing-a-single-role)
- [Installing multiple roles](#installing-multiple-roles)

[Installing Themes](#installing-themes)
- [Installing a single Theme](#installing-a-single-theme)
- [Installing multiple Themes](#installing-multiple-themes)

[Installing Views](#installing-views)
- [Installing a single View](#installing-a-single-view)
- [Installing multiple Views](#installing-multiple-views)

[Installing Vocabularies](#installing-vocabularies)
- [Installing a single Vocabulary](#installing-a-single-vocabulary)
- [Installing multiple Vocabularies](#installing-multiple-vocabularies)

# Introduction
The Installs API resides inside a single trait found at <code>Drupal\Tests\test_support\Traits\Installs\InstallsExportedConfig</code>.

Its purpose is to allow developers to install exported configuration during test runs.

The `InstallsExportedConfig` trait can install just about any exported configuration file, but there are other traits it makes use of to provide a better developer experience when importing certain configuration.

## Strict Configuration
During some test runs, you may run into schema errors when importing configuration. [Although not recommended](https://www.drupal.org/node/2391795), you can disable strict schema checks when importing configuration.

Schema errors can often happen when other dependent configuration files haven't been imported. It may be the case that your test does not explicitly need them, so strict config schema checks may not matter under test. For this reason, we provide two fluent methods to enable or disable strict schema checks.

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

#### Installing Entity Type and Bundles at the same time
You can also install the entity schema for a given entity type along with a list of bundles you would also like installed.

##### Installing Entity Type with single Bundle
```php
public function install_entity_type_with_single_bundle(): void
{
    $this->installEntitySchemaWithBundles('node', 'page');
}
```

##### Installing Entity Type with multiple Bundles
```php
public function install_entity_type_with_multiple_bundles(): void
{
    $this->installEntitySchemaWithBundles('node', [
        'page',
        'news',
    ]);
}
```

### Installing Fields
There is a specific trait called [InstallsFields](./tests/src/Traits/InstallsFields.php) that provides a few helpful methods to install field definitions for a particular entity type and an entity types bundle.

The trait will install the `field` module for you so that field configurations can be installed during test runs.
#### Installing a single field for an entity type
To install a single field on an entity type, call the `installField` method along with the name of the field configuration you want to install and the ID of the entity type.

```php
public function install_single_field_for_entity_type(): void
{
    $this->installField('field_author', 'my_entity_type');
}
```

#### Installing multiple fields for an entity type
To install multiple fields on an entity type, call the `installFields` method along with an array of the names of the field configurations you want to install and the ID of the entity type.

```php
public function install_multiple_fields_for_entity_type(): void
{
    $fieldsToInstall = [
        'field_author',
        'field_category'
    ];

    $this->installFields($fieldsToInstall, 'my_entity_type');
}
```

#### Installing a single field for a bundle of an entity type
To install a single field on an entity type bundle, call the `installField` method along with the name of the field configuration you want to install, the ID of the entity type and the ID of the bundle.

```php
public function install_single_field_for_entity_type_bundle(): void
{
    $this->installField('body', 'node', 'page');
}
```

#### Installing multiple field for a bundle of an entity type
To install multiple fields on an entity type bundle, call the `installFields` method along with an array of the names of the field configurations you want to install, the ID of the entity type and the ID of the bundle.

```php
public function install_multiple_fields_for_entity_type_bundle(): void
{
    $fieldsToInstall = [
        'body',
        'field_author',
    ];

    $this->installFields($fieldsToInstall, 'node', 'page');
}
```

#### Installing all fields for an Entity Type
To install all fields available from configuration on an entity type, call the `installAllFieldsForEntity` method along with the entity type ID you want to target.

The example below will install all field configurations available for the `custom_entity` entity type.
```php
public function install_all_fields_for_entity_type(): void
{
    $this->installAllFieldsForEntity('custom_entity');
}
```

#### Installing all fields for an Entity Type Bundle
To install all fields available from configuration on an entity type's bundle, call the `installAllFieldsForEntity` method along with the entity type ID and the bundle ID.

The example below will install all field configurations available for the page bundle of the node entity type.
```php
public function install_all_fields_for_entity_type_bundle(): void
{
    $this->installAllFieldsForEntity('node', 'page');
}
```

### Installing Image Styles
There is a specific trait called [InstallsImageStyles](./tests/src/Traits/Installs/InstallsImageStyles.php) that provides a helpful method called `installImageStyles`.

The trait will install the `image` module as well as install the `image_style` entity schema whenever an image style is being installed from configuration.

#### Installing a single Image Style
To install a single image style, call the `installImageStyles` method and pass a single image style ID you want to install.
```php
public function install_single_image_style(): void
{
    $this->installImageStyles('large');
}
```

#### Installing multiple Image Styles
To install multiple image styles, call the `installImageStyles` method and pass an array of image style ID's you want to install.
```php
public function install_multiple_image_styles(): void
{
    $this->installImageStyles([
        'large',
        'medium',
    ]);
}
```

### Installing menus
There is a specific trait called [InstallsMenus](./tests/src/Traits/Installs/InstallsMenus.php) that provides a helpful method called `installMenus`.

The trait will install the `system` module as well as install the `menu` entity schema whenever an image style is being installed from configuration.
#### Installing a single menu
To install a single menu, call the `installMenus` method and pass a single menu ID you want to install
```php
public function install_single_menu(): void
{
    $this->installMenus('header');
}
```
#### Installing multiple menus
To install multiple menus, call the `installsMenus` method and pass an array of menu ID's you want to install
```php
public function install_multiple_menus(): void
{
    $this->installMenus([
        'header',
        'footer',
    ])
}
```

### Installing Modules
There is a specific trait called [InstallsModules](./tests/src/Traits/Installs/InstallsModules.php) that provides a single method called `enableModuleWithDependencies`. The purpose of this method is to improve the developer experience of preparing a kernel test.

Take `my_custom_module` for example. This may declare a dependency on the `text` module. The `text` module also declares the `field` and `filter` modules as dependencies.

Using `enableModuleWithDependencies` will install `my_custom_module`, `text`, `field`, and `filter`, as it attempts to recursively enable all dependencies declared.

This is useful for finding out whether your custom module is correct. E.G. If you have a custom module that uses a `text` field type via a `BaseFieldDefinition`, then your module should probably declare a dependency on the `text` module.

#### Installing a module and its dependencies (recursively)
To install a module and its depenendencies recursively, call the `enableModuleWithDependencies` method.
```php
public function recursively_enable_module_and_dependencies(): void
{
    // we expect the text, field and filter modules to
    // be enabled because the text module declares
    // the field and filter as dependencies.
    $this->enableModuleWithDependencies('text');
}
```

### Installing Roles
There is a specific trait called [InstallsRoles](./tests/src/Traits/Installs/InstallsRoles.php) that provides a single method called `installRoles`.

When installing a role, the trait will enable the `system` and `user` modules along with installing the entity schema for the `user_role` entity type.

#### Installing a single role
To install a single role, call the `installRoles` method with a single role ID.
```php
public function install_single_role(): void
{
    $this->installRoles('editor');
}
```

#### Installing multiple roles
To install multiple roles, call the `installRoles` method with an array of role ID's.
```php
public function install_multiple_roles(): void
{
    $this->installRoles([
        'editor',
        'writer',
    ]);
}
```

### Installing themes
There is a specific trait called [InstallsThemes](./tests/src/Traits/Installs/InstallsThemes.php) that provides a single method called `installThemes`.

When installing a theme, the trait will enable the `system` module and install its schema.

#### Installing a single theme
To install a single theme, call the `installThemes` method and pass a single argument that is the theme you want to install.
```php
public function install_single_theme(): void
{
    $this->installThemes('stark');
}
```

#### Installing multiple themes
To install multiple themes, call the `installThemes` method and pass an array of themes you want to install.
```php
public function install_multiple_themes(): void
{
    $this->installThemes([
        'stark',
        'classy',
    ]);
}
```

### Installing views
There is a specific trait called [InstallsViews](./tests/src/Traits/Installs/InstallsViews.php) that provides a single method called `installViews`.

The trait will enable the `system`, `user`, and `views` modules along with installing the `view` entity schema.

#### Installing a single View
To install a single view, call the `installViews` method and pass a single argument of the Views ID.

```php
public function install_single_view(): void
{
    $this->installViews('content');
}
```

#### Installing multiple Views
To install multiple views, call the `installViews` method with an array of View ID's.
```php
public function install_multiple_views(): void
{
    $this->installViews([
        'content',
        'media',
    ])
}
```

### Installing Vocabularies
There is a specific trait called [InstallingVocabularies](./tests/src/Traits/Installs/InstallingVocabularies.php) that provides a single method called `installVocabularies`.

The trait will enable the `taxonomy` modules along with installing the `taxonomy_vocabulary` entity schema.

#### Installing a single vocabulary
To install a single vocabuary, call the `installVocabularies` method with an argument of the vocabulary name.

```php
public function install_single_vocabulary(): void
{
    $this->installVocabularies('tags');
}
```
#### Installing multiple vocabularies
To install multiple vocabularies, call the `installVocabularies` method with an argument of an array of vocabularies.

```php
public function install_multiple_vocabularies(): void
{
    $this->installVocabularies([
        'tags',
        'category',
    ]);
}
```
