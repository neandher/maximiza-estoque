<?php

namespace App\EventListener;

use App\Entity\Customer;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CustomerSubscriber implements EventSubscriber
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * CustomerSubscriber constructor.
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return array The event names to listen to
     */
    public function getSubscribedEvents()
    {
        return [
            'prePersist'
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        /** @var Customer $customer */
        $customer = $args->getObject();

        if ($customer instanceof Customer && $token = $this->tokenStorage->getToken()) {
            $customer->setUser($token->getUser());
        }
    }
}