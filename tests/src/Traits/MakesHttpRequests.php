<?php

namespace Drupal\Tests\test_traits\Traits;

use Drupal\Tests\test_traits\Traits\Response\TestResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait MakesHttpRequests
{
    /** @var bool */
    private $followRedirects = false;

    /** @var bool */
    private $requestIsAjax = false;

    /** @var array */
    private $headers = [];

    public function get(string $uri, array $headers = []): TestResponse
    {
        return $this->call('GET', $uri, [], [], [], $headers);
    }

    public function getJson(string $uri, array $headers = []): TestResponse
    {
        return $this->json('GET', $uri, [], $headers);
    }

    public function post($uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->call('POST', $uri, $data, [], [], $headers);
    }

    public function postJson($uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->json('POST', $uri, $data, $headers);
    }

    public function put($uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->call('PUT', $uri, $data, [], [], $headers);
    }

    public function putJson(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->json('PUT', $uri, $data, $headers);
    }

    public function patch(string $uri, array $data = [], array $headers = []): TestResponse
    {
        $server = $this->transformHeadersToServerVars($headers);
        $cookies = [];

        return $this->call('PATCH', $uri, $data, $cookies, [], $server);
    }

    public function patchJson(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->json('PATCH', $uri, $data, $headers);
    }

    public function options(string $uri, array $data = [], array $headers = []): TestResponse
    {
        $server = $this->transformHeadersToServerVars($headers);
        $cookies = [];

        return $this->call('OPTIONS', $uri, $data, $cookies, [], $server);
    }

    public function optionsJson(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->json('OPTIONS', $uri, $data, $headers);
    }

    public function delete($uri, array $data = [], array $headers = []): TestResponse
    {
        $server = $this->transformHeadersToServerVars($headers);
        $cookies = [];

        return $this->call('DELETE', $uri, $data, $cookies, [], $server);
    }

    public function deleteJson($uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->json('DELETE', $uri, $data, $headers);
    }

    public function ajax(): self
    {
        $this->headers = array_merge($this->headers, [
            'X-Requested-With' => 'XMLHttpRequest'
        ]);

        return $this;
    }

    public function json(string $method, string $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null): TestResponse
    {
        $headers = array_merge([
            'CONTENT_LENGTH' => mb_strlen($content, '8bit'),
            'CONTENT_TYPE' => 'application/json',
            'Accept' => 'application/json',
        ], $server);

        return $this->call(
            $method,
            $uri,
            [],
            $cookies,
            $files,
            $headers,
            $content
        );
    }

    /** @return mixed */
    public function call(string $method, string $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null): TestResponse
    {
        $request = Request::create($uri, $method, $parameters, $cookies, $files, $server, $content);

        $request->setSession($this->container->get('session'));

        if ($this->headers) {
            foreach ($this->headers as $header => $value) {
                $request->headers->set($header, $value);
            }
        }

        $httpKernel = $this->container->get('http_kernel');

        $response = $httpKernel->handle($request);

        $httpKernel->terminate($request, $response);

        if ($this->followRedirects) {
            $response = $this->followRedirects($response);

            $this->followRedirects = false;
        }

        $kernel = $this->container->get('kernel');

        $kernel->invalidateContainer();
        $kernel->rebuildContainer();

        return TestResponse::fromBaseResponse($response);
    }

    public function followingRedirects()
    {
        $this->followRedirects = true;

        return $this;
    }

    public function from(string $url): self
    {
//        $this->headers = array_merge($this->headers, $this->transformHeadersToServerVars([
//            'referer' => $url,
//        ]));

        $this->headers = array_merge($this->headers, [
            'referer' => $url,
        ]);

        $this->headers = array_merge($this->headers, [
            'expires' => 'never',
        ]);

        return $this;
    }

    /**
     * Transform headers array to array of $_SERVER vars with HTTP_* format.
     *
     * @param  array  $headers
     * @return array
     */
    protected function transformHeadersToServerVars(array $headers)
    {
        return collect(array_merge($this->headers, $headers))->mapWithKeys(function ($value, $name) {
            $name = strtr(strtoupper($name), '-', '_');

            return [$this->formatServerHeaderKey($name) => $value];
        })->all();
    }

    protected function formatServerHeaderKey($name)
    {
        if (! str_starts_with($name, 'HTTP_') && $name !== 'CONTENT_TYPE' && $name !== 'REMOTE_ADDR') {
            return 'HTTP_'.$name;
        }

        return $name;
    }

    private function followRedirects(Response $response)
    {
        $this->followRedirects = false;

        while ($response->isRedirect()) {
            $response = $this->get($response->headers->get('Location'));
        }

        return $response;
    }
}
