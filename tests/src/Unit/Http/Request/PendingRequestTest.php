<?php

namespace Drupal\Tests\test_support\Unit\Http\Request;

use Drupal\Tests\test_support\Traits\Http\Request\PendingRequest;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\HttpFoundation\Request;

class PendingRequestTest extends UnitTestCase
{
    /** @test */
    public function with_header(): void
    {
        $request = $this->createPendingRequest();

        $request->withHeader('Content-Type', 'application/json');

        $this->assertEquals($request->getOriginal()->headers->get('Content-Type'), 'application/json');
    }

    /** @test */
    public function with_headers(): void
    {
        $request = $this->createPendingRequest();

        $request->withHeaders([
            'Content-Type' => 'application/json',
            'referer' => 'https://www.example.com/referer',
        ]);

        $this->assertEquals($request->getOriginal()->headers->get('Content-Type'), 'application/json');
        $this->assertEquals($request->getOriginal()->headers->get('referer'), 'https://www.example.com/referer');
    }

    /** @test */
    public function from_url(): void
    {
        $request = $this->createPendingRequest();

        $refererUrl = 'https://www.example.com/referer-from-url';

        $request->from($refererUrl);

        $this->assertEquals($request->getOriginal()->headers->get('referer'), $refererUrl);
    }

    /** @test */
    public function ajax_requested_with(): void
    {
        $request = $this->createPendingRequest();

        $request->ajax();

        $this->assertEquals($request->getOriginal()->headers->get('X-Requested-With'), 'XMLHttpRequest');
    }

    /** @test */
    public function as_form(): void
    {
        $request = $this->createPendingRequest();

        $request->asForm();

        $this->assertEquals($request->getOriginal()->headers->get('Content-Type'), 'application/x-www-form-urlencoded');
    }

    /** @test */
    public function as_json(): void
    {
        $request = $this->createPendingRequest();

        $request->asJson();

        $this->assertEquals($request->getOriginal()->headers->get('Content-Type'), 'application/json');
    }

    /** @test */
    public function proxies_calls_to_symfony_request(): void
    {
        $uri = 'https://www.example.com/proxy-call';

        $request = $this->createPendingRequest(Request::create($uri));

        $this->assertEquals($request->getUri(), $uri);
    }

    public function proxies_gets_to_symfony_request(): void
    {
        $request = $this->createPendingRequest();

        $request->withHeader('Content-Type', 'application/json');

        $this->assertEquals($request->headers->get('Content-Type'), 'application/json');
    }

    private function createPendingRequest(?Request $request = null): PendingRequest
    {
        if ($request === null) {
            $request = Request::create('https://www.example.com/');
        }

        return PendingRequest::createFromSymfonyRequest($request);
    }
}
