<?php

namespace App\EventListener;

use App\Entity\Customer;
use App\Entity\CustomerObservations;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CustomerObservationSubscriber implements EventSubscriber
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * CustomerObservationSubscriber constructor.
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
        /** @var CustomerObservations $customer */
        $customerObservation = $args->getObject();

        if ($customerObservation instanceof CustomerObservations && $token = $this->tokenStorage->getToken()) {
            $customerObservation->setUser($token->getUser());
        }
    }
}