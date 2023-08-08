##### Table of Contents
[Introduction](#introduction)

[HTTP (MakesHttpRequests)](#http)
- [GET Requests](#get-requests)
  - [Making a GET request](#making-a-get-request)
  - [Making a GET request using JSON](#making-a-get-request-using-json)

# Introduction
This package aims to provide useful API's that can be used when writing automated testing for Drupal applications. The overall aim of this package is to improve the developer experience when writing automated tests, particularly Kernel tests.

## HTTP
The HTTP API provided by this module resides inside a single trait located at <code>Drupal\Tests\test_support\Traits\Http\MakesHttpRequests</code>.

Its purpose is to allow developers to make HTTP requests to the Drupal application under test and assert against the contents of the response and the response itself.

Most methods that exist inside the <code>MakesHttpRequests</code> trait will return an instance of <code>TestResponse</code>. More on this later!

The examples below demonstrate how each method may be used inside a test that's using the <code>MakesHttpRequests</code> trait.

### GET Requests
#### Making a GET request
```php
/** @test */
public function http_get(): void
{
    $this->get($url)->assertOk();
}
```

#### Making a GET request using JSON
```php
public function http_get_json(): void
{
    $this->getJson($url)->assertOK();
}
```
