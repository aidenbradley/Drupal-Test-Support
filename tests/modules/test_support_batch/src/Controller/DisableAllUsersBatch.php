<?php

namespace Drupal\test_support_batch\Controller;

use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class DisableAllUsersBatch implements ContainerInjectionInterface
{
    /** @var EntityStorageInterface */
    private $userStorage;

    public static function create(ContainerInterface $container): self
    {
        return new self(
            $container->get('entity_type.manager')->getStorage('user')
        );
    }

    public function __construct(EntityStorageInterface $userStorage)
    {
        $this->userStorage = $userStorage;
    }

    public function prepareBatch(): Response
    {
        $builder = new BatchBuilder();
        $builder->setTitle('Disable Users')
            ->setInitMessage('Disabling users. Processed @current.')
            ->setProgressMessage('Processed @current out of @total.')
            ->setErrorMessage('Batch has encountered an error.');

        /** @var \Drupal\user\Entity\User $user */
        foreach ($this->userStorage->loadMultiple() as $user) {
            $builder->addOperation([$this, 'disableUser'], [$user]);
        }

        batch_set($builder->toArray());

        $response = new Response('', Response::HTTP_NO_CONTENT);

        return $response;
    }

    public function prepareBatchAndProcess(): RedirectResponse
    {
        $this->prepareBatch();

        $redirect = batch_process('/');

        if ($redirect instanceof RedirectResponse) {
            return $redirect;
        }

        return new RedirectResponse('/');
    }

    public function disableUser(UserInterface $user): void
    {
        $user->set('status', 0)->save();
    }
}
