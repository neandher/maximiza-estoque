<?php

namespace App\EventListener;

use App\Entity\Stock;
use App\Event\StockEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class StockCreateSubscriber implements EventSubscriberInterface
{

    /**
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
          StockEvents::STOCK_CREATE_COMPLETED => 'onCreateCompleted'
        ];
    }

    public function onCreateCompleted(GenericEvent $event)
    {
        /** @var Stock[] $stocks */
        $stocks = $event->getSubject();

        /*foreach ($stocks as $stock){
            //
        }*/
    }
}