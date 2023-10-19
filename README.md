##### Table of Contents
[Introduction](#introduction)

[HTTP - Make HTTP requests in your Kernel tests](#http)

[Installs - Install exported configuration in your Kernel tests](#installs)

[Support - Developer friendly API's to improve your testing experience](#support)

# Introduction
This package aims to provide useful API's to improve the developer experience when writing automated tests for Drupal applications, particularly in Kernel tests.

## HTTP ([HTTP API Documentation](./documentation/readme_http.md))
The purpose of the HTTP API is to give developers the ability to make HTTP requests to the Drupal application under test. You can assert against the contents of the response and even the response itself.

The `HTTP` API is found inside a single trait called [MakesHttpRequests](./tests/src/Traits/Http/MakesHttpRequests.php).

## Installs ([Installs API Documentation](./documentation/readme_installs.md))
The purpose of the Installs API is to allow developers to install any exported configuration and write tests against it. Configuration can be installed even when developing for multi-site!

The `Installs` API resides a single trait called [InstallsExportedConfig](./tests/src/Traits/Installs/InstallsExportedConfig.php).

Here is a list of areas the Installs API aims to improve -
- Installing Blocks
- Installing Entity Types
- Installing Bundles
- Installing Fields
- Installing Image Styles
- Installing Menus
- Installing Themes
- Installing Views
- Installing Vocabluaries
- Installing Modules

## Support ([Support API Documentation](./documentation/readme_support.md))
The purpose of the Support API is to provide convenient methods to improve the developer experience when writing automated tests.

There is no single trait for the Support API. Rather there are many traits, where each trait aims to address the developer experience when working with certain areas of Drupal and automated testing.

Here is an overview of what the Support API aims to improve when writing and running automated tests -
- Running batches
- Running system cron
- Time Travel (travelling to a point in time)
  - This includes timezone support
- Creating and updating entities
- Installing languages and setting the current language
- Testing any emails that are sent by Drupal
- Running Drupal Queues
- Running update hooks, even when they use batching for the following -
  - Update hooks
  - Post Update hooks
  - Deploy hooks
- Testing against Events
- Testing against Event Subscribers
