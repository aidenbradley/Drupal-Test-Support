<?php

namespace Drupal\Tests\test_support\Traits\Http;

use Drupal\Tests\test_support\Traits\Http\Response\TestResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait MakesHttpRequests
{
    /** @var bool */
    private $followRedirects = false;

    /** @var array<string>|array<array<string>> */
    private $headers = [];

    /** @param array<mixed> $headers */
    public function get(string $uri, array $headers = []): TestResponse
    {
        return $this->call('GET', $uri, [], [], [], $headers);
    }

    /** @param array<mixed> $headers */
    public function getJson(string $uri, array $headers = []): TestResponse
    {
        return $this->json('GET', $uri, [], $headers);
    }

    /**
     * @param array<mixed> $data
     * @param array<mixed> $headers
     */
    public function post(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->call('POST', $uri, $data, [], [], $headers);
    }

    /**
     * @param array<mixed> $data
     * @param array<mixed> $headers
     */
    public function postJson(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->json('POST', $uri, $data, $headers);
    }

    /**
     * @param array<mixed> $data
     * @param array<mixed> $headers
     */
    public function put(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->call('PUT', $uri, $data, [], [], $headers);
    }

    /**
     * @param array<mixed> $data
     * @param array<mixed> $headers
     */
    public function putJson(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->json('PUT', $uri, $data, $headers);
    }

    /**
     * @param array<mixed> $data
     * @param array<mixed> $headers
     */
    public function patch(string $uri, array $data = [], array $headers = []): TestResponse
    {
        $server = $this->transformHeadersToServerVars($headers);
        $cookies = [];

        return $this->call('PATCH', $uri, $data, $cookies, [], $server);
    }

    /**
     * @param array<mixed> $data
     * @param array<mixed> $headers
     */
    public function patchJson(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->json('PATCH', $uri, $data, $headers);
    }

    /**
     * @param array<mixed> $data
     * @param array<mixed> $headers
     */
    public function options(string $uri, array $data = [], array $headers = []): TestResponse
    {
        $server = $this->transformHeadersToServerVars($headers);
        $cookies = [];

        return $this->call('OPTIONS', $uri, $data, $cookies, [], $server);
    }

    /**
     * @param array<mixed> $data
     * @param array<mixed> $headers
     */
    public function optionsJson(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->json('OPTIONS', $uri, $data, $headers);
    }

    /**
     * @param array<mixed> $data
     * @param array<mixed> $headers
     */
    public function delete(string $uri, array $data = [], array $headers = []): TestResponse
    {
        $server = $this->transformHeadersToServerVars($headers);
        $cookies = [];

        return $this->call('DELETE', $uri, $data, $cookies, [], $server);
    }

    /**
     * @param array<mixed> $data
     * @param array<mixed> $headers
     */
    public function deleteJson(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->json('DELETE', $uri, $data, $headers);
    }

    public function ajax(): self
    {
        $this->headers = array_merge($this->headers, [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        return $this;
    }

    public function asForm(): self
    {
        return $this->withHeader('Content-Type', 'application/x-www-form-urlencoded');
    }

    public function asJson(): self
    {
        return $this->withHeader('Content-Type', 'application/json');
    }

    /**
     * @param null|resource|string $content
     * @param array<mixed> $cookies
     * @param array<mixed> $files
     * @param array<mixed> $server
     * @param resource|string|null $content
     */
    public function json(string $method, string $uri, array $cookies = [], array $files = [], array $server = [], $content = null): TestResponse
    {
        $headers = array_merge([
            'CONTENT_TYPE' => 'application/json',
            'Accept' => 'application/json',
        ], $server);

        if ($content !== null) {
            $length = '';

            if (is_resource($content)) {
                $length = fstat($content)['size'] ?? '';
            }

            if (is_string($content)) {
                $length = mb_strlen($content, '8bit');
            }

            $headers['CONTENT_LENGTH'] = $length;
        }

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

    /**
     * @param null|resource|string $content
     * @param array<mixed> $parameters
     * @param array<mixed> $cookies
     * @param array<mixed> $files
     * @param array<mixed> $server
     * @param resource|string|null $content
     */
    public function call(string $method, string $uri, array $parameters = [], array $cookies = [], array $files = [], array $server = [], $content = null): TestResponse
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

        /** @phpstan-ignore-next-line */
        $kernel->invalidateContainer();

        /** @phpstan-ignore-next-line */
        $kernel->rebuildContainer();

        return TestResponse::fromBaseResponse($response);
    }

    public function followingRedirects(): self
    {
        $this->followRedirects = true;

        return $this;
    }

    public function from(string $url): self
    {
        return $this->withHeader('referer', $url);
    }

    /** @param array<string>|array<array<string>> $headers */
    protected function withHeaders(array $headers): self
    {
        $this->headers = array_merge($this->headers, $headers);

        return $this;
    }

    /** @param string|array<string> $value */
    protected function withHeader(string $header, $value): self
    {
        $this->headers = array_merge($this->headers, [
            $header => $value,
        ]);

        return $this;
    }

    /**
     * Transform headers array to array of $_SERVER vars with HTTP_* format.
     *
     * @param array<mixed> $headers
     * @return array<mixed>
     */
    protected function transformHeadersToServerVars(array $headers)
    {
        return collect(array_merge($this->headers, $headers))->mapWithKeys(function ($value, $name) {
            $name = strtr(strtoupper($name), '-', '_');

            return [
                $this->formatServerHeaderKey($name) => $value,
            ];
        })->all();
    }

    protected function formatServerHeaderKey(string $name): string
    {
        if (! str_starts_with($name, 'HTTP_') && $name !== 'CONTENT_TYPE' && $name !== 'REMOTE_ADDR') {
            return 'HTTP_' . $name;
        }

        return $name;
    }

    private function followRedirects(Response $response): Response
    {
        $this->followRedirects = false;

        while ($response->isRedirect()) {
            $location = $response->headers->get('Location');

            if ($location === null) {
                break;
            }

            $response = $this->get($location);
        }

        return $response;
    }
}
