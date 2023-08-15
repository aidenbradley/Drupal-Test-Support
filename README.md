##### Table of Contents
[Introduction](#introduction)

[HTTP - Make HTTP requests in your Kernel tests](#http)

[Installs - Install exported configuration in your Kernel tests](#installs)

[Support - Developer friendly API's to improve your testing experience](#support)

# Introduction
This package aims to provide useful API's that can be used when writing automated testing for Drupal applications. The overall aim of this package is to improve the developer experience when writing automated tests, particularly <b>Kernel tests</b>.

## HTTP ([HTTP API Documentation](./documentation/readme_http.md))
The `HTTP` API resides inside a single trait located at <code>Drupal\Tests\test_support\Traits\Http\MakesHttpRequests</code>.

Its purpose is to allow developers to make HTTP requests to the Drupal application under test and assert against the contents of the response and the response itself.

## Installs [Installs API Documentation](./documentation/readme_installs.md)
The `Installs` API resides a single trait located at `Drupal\Tests\test_support\Traits\Installs\InstallsExportedConfig`.

Its purpose is to allow developers to install any exported configuration and write tests against it.

A perfect example of this is installing field configuration. Instead of having to create the field configuration in your Kernel test, you can now tell the test to install that field configuration.

This works regardless of whether your Drupal application is a single site or a multi-site.

From this API, developers can install virtually any exported configuration.

There are specific traits that `InstallsExportedConfig` uses. These traits attempt to set up the necessary dependencies during the test run. This includes enabling required modules and installing entity schemas.

Here is a list of areas this API aims to improve -
- Installing Blocks
- Installing Entity Types
- Installing  Bundles
- Installing  Image Styles
- Installing  Menus
- Installing Themes
- Installing Views
- Installing Vocabluaries
- Enabling Modules

