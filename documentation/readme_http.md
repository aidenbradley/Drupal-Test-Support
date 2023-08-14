[GET Requests](#get-requests)
- [Making a GET request](#making-a-get-request)
- [Making a GET request using JSON](#making-a-get-request-using-json)

[POST Requests](#post-requests)
- [Making a POST request](#making-a-post-request)
- [Making a POST request using JSON](#making-a-post-request-using-json)

[PUT Requests](#put-requests)
- [Making a PUT request](#making-a-put-request)
- [Making a PUT request using JSON](#making-a-put-request-using-json)

[PATCH Requests](#patch-requests)
- [Making a PATCH request](#making-a-patch-request)
- [Making a PATCH request using JSON](#making-a-patch-request-using-json)

[DELETE Requests](#delete-requests)
- [Making a DELETE request](#making-a-delete-request)
- [Making a DELETE request using JSON](#making-a-delete-request-using-json)

[Making AJAX / XML HTTP Requests](#making-ajax-xml-http-requests)

[Making a request from a referrer URL](#making-a-request-from-a-referrer-url)

[Making a request as a form](#making-a-request-as-a-form)

[Making a request as JSON](#making-a-request-as-json)

[Following redirects when making an HTTP Request](#following-redirects-when-making-an-http-request)
# Introduction
This package aims to provide useful API's that can be used when writing automated testing for Drupal applications. The overall aim of this package is to improve the developer experience when writing automated tests, particularly Kernel tests.

## HTTP
The HTTP API resides inside a single trait located at <code>Drupal\Tests\test_support\Traits\Http\MakesHttpRequests</code>.

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
    $this->getJson($url)->assertOk();
}
```
### POST Requests
#### Making a POST request
```php
public function http_post(): void
{
    $this->post($url)->assertOk();
}
```

#### Making a POST request using JSON
```php
public function http_post_json(): void
{
    $this->postJson($url)->assertOk();
}
```

### PUT Requests
#### Making a PUT request
```php
public function http_put(): void
{
    $this->put($url)->assertOk();
}
```
#### Making a PUT request using JSON
```php
public function http_put_json(): void
{
    $this->putJson($url)->assertOk();
}
```

### PATCH Requests
#### Making a PATCH request
```php
public function http_patch(): void
{
    $this->patch($url)->assertOk();
}
```
#### Making a PATCH request using JSON
```php
public function http_patch_json(): void
{
    $this->patchJson($url)->assertOk();
}
```

### DELETE Requests
#### Making a DELETE request
```php
public function http_delete(): void
{
    $this->delete($url)->assertOk();
}
```
#### Making a DELETE request using JSON
```php
public function http_delete_json(): void
{
    $this->deleteJson($url)->assertOk();
}
```

### OPTIONS Requests
#### Making a OPTIONS request
```php
public function http_options(): void
{
    $this->options($url)->assertOk();
}
```

### Making AJAX / XML HTTP Requests
To make AJAX / XML HTTP requests, simlpy call the `ajax` method before calling your HTTP Verb.

To do this, simply call the `ajax` method.

Calling the `ajax` method will set the `X-Requested-With` header to `XMLHttpRequest`.
```php
public function ajax(): void
{
    $this->ajax()->post($url, [
        'data' => 'example'
    ])
}
```

### Making a request from a referrer URL
You may want to inform your test that the `GET` request is being made from another URL as a referrer.

To do this, simply call the `from` method.

Calling the `from` method will set the `referer` header to whatever URL you set.

```php
public function from_referer(): void
{
    $this->from($refererUrl)->get($url);
}
```

### Making a request as a form
You may want to make your `POST` request as a form.

To do this, simply call the `asForm` method before making your `POST` request.

```php
public function send_as_form(): void
{
    $this->asForm()->post($url);
}
```

### Making a request as JSON
You may want to make your `POST` request as JSON.

To do this, simply call the `asJson` method before making your `POST` request.

```php
public function send_as_json(): void
{
    $this->asJson()->post($url);
}
```

### Following redirects when making an HTTP Request
When making a `GET` request, you can tell the test to follow any redirect responses that are returned. This is useful for testing routes that are behind authentication, for example.

Informing the test to follow redirects means the test will keep making `GET` requests to the next location until it finds a response that is not a redirect.

To do this, simply call the `followingRedirects` method before making your `GET` request.
```php
public function following_redirects(): void
{
    $this->followingRedirects()->get($authenticatedUrl)->assertLocation($loginUrl);
}
```
