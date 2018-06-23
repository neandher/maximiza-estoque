<?php

namespace App\EventListener;

use App\Entity\Stock;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class StockSubscriber implements EventSubscriber
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * StockAddSubscriber constructor.
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
        /** @var Stock $stock */
        $stock = $args->getObject();

        if ($stock instanceof Stock && $token = $this->tokenStorage->getToken())
            $stock->setUser($token->getUser());

    }
}