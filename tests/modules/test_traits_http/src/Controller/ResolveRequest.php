<?php

namespace Drupal\test_traits_http\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResolveRequest implements ContainerInjectionInterface
{
    /** @var Request */
    private $request;

    public static function create(ContainerInterface $container)
    {
        return new self(
            $container->get('request_stack')->getCurrentRequest(),
        );
    }

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function __invoke(): Response
    {
        return Response::create('content');
    }

    public function xmlHttpOnly(): Response
    {
        if ($this->request->isXmlHttpRequest() === false) {
            throw new NotFoundHttpException();
        }

        return Response::create();
    }

    public function json(): Response
    {
        if ($this->request->getContentType() !== 'json') {
            throw new NotFoundHttpException();
        }

        return JsonResponse::create();
    }

    public function redirect(?string $redirectRoute = null): Response
    {
        if ($redirectRoute !== null) {
            return RedirectResponse::create(
                Url::fromRoute($redirectRoute)->toString(true)->getGeneratedUrl()
            );
        }

        return Response::create();
    }

    public function basicAuth(): Response
    {
        return JsonResponse::create();
    }
}
