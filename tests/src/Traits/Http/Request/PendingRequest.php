<?php

namespace Drupal\Tests\test_support\Traits\Http\Request;

use Symfony\Component\HttpFoundation\Request;

class PendingRequest
{
    /** @var array */
    private $headers;

    public static function make(): self
    {
        return new self();
    }

    /** @param mixed $value */
    public function withHeader(string $header, $value): self
    {
        $this->headers[$header] = $value;

        return $this;
    }

    public function withHeaders(array $headers)
    {
        foreach ($headers as $header => $value) {
            $this->withHeader($header, $value);
        }

        return $this;
    }

    public function from(string $url): void
    {
        $this->withHeader('referer', $url);
    }

    public function ajax(): self
    {
        return $this->withHeader('X-Requested-With', 'XMLHttpRequest');
    }

    public function asForm(): self
    {
        return $this->withHeader('Content-Type', 'application/x-www-form-urlencoded');
    }

    public function asJson(): self
    {
        return $this->withHeader('Content-Type', 'application/json');
    }

    public function create($uri, $method = 'GET', $parameters = [], $cookies = [], $files = [], $server = [], $content = null): Request
    {
        $request = Request::create(...func_get_args());

        foreach ($this->headers as $header => $value) {
            $request->headers->set($header, $value);
        }

        return $request;
    }

    /** @return mixed */
    public function __call($name, $arguments)
    {
        $return = null;

        if (method_exists($this->symfonyRequest, $name)) {
            $return = $this->symfonyRequest->$name(...$arguments);
        }

        return $return instanceof Request ? $this : $return;
    }

    /** @return mixed */
    public function __get($name)
    {
        if (property_exists($this->symfonyRequest, $name)) {
            return $this->symfonyRequest->$name;
        }

        return $this->$name;
    }
}
