<?php

namespace Drupal\Tests\test_support\Traits\Http\Request;

use Symfony\Component\HttpFoundation\Request;

class PendingRequest
{
    /** @var Request */
    private $symfonyRequest;

    public static function createFromSymfonyRequest(Request $request): self
    {
        return new self($request);
    }

    public function __construct(Request $request)
    {
        $this->symfonyRequest = $request;
    }

    /** @param mixed $value */
    public function withHeader(string $header, $value): self
    {
        $this->symfonyRequest->headers->set($header, $value);

        return $this;
    }

    public function withHeaders(array $headers)
    {
        foreach ($headers as $header => $value) {
            $this->withHeader($header, $value);
        }

        return $this;
    }

    public function from(string $url): self
    {
        return $this->withHeader('referer', $url);
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

    public function getOriginal(): Request
    {
        return $this->symfonyRequest;
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
