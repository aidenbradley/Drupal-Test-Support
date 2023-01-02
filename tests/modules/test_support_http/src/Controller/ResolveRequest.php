<?php

namespace Drupal\test_support_http\Controller;

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
        $content = '';

        if ($this->request->query->has('headers')) {
            $content = $this->request->headers->getIterator()->getArrayCopy();
        }

        $jsonResponse = new JsonResponse($content);

        return $jsonResponse;
    }

    public function xmlHttpOnly(): Response
    {
        if ($this->request->isXmlHttpRequest() === false) {
            throw new NotFoundHttpException();
        }

        return new Response();
    }

    public function json(): Response
    {
        if ($this->request->getContentType() !== 'json') {
            throw new NotFoundHttpException();
        }

        return new JsonResponse();
    }

    public function redirect(?string $redirectRoute = null): Response
    {
        if ($redirectRoute !== null) {
            $redirectResponse = new RedirectResponse(
                Url::fromRoute($redirectRoute)->toString(true)->getGeneratedUrl()
            );

            return $redirectResponse;
        }

        return new Response();
    }

    public function redirectFromExample(?string $redirectRoute = null): Response
    {
        if ($this->request->headers->get('referer') === 'https://example.com/from') {
            $redirectResponse = new RedirectResponse(
                Url::fromRoute($redirectRoute)->toString(true)->getGeneratedUrl()
            );

            return $redirectResponse;
        }

        return new Response();
    }
}
