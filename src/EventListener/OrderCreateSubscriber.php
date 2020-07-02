<?php

namespace App\EventListener;

use App\Entity\Order;
use App\Entity\Stock;
use App\StockTypes;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class OrderCreateSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            Events::onFlush,
        ];
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $keyEntity => $entity) {
            if ($entity instanceof Order) {
                foreach ($entity->getOrderItems() as $orderItem) {
                    $stockByReferency = $em->getRepository(Stock::class)->findOneBy(['referency' => $orderItem->getReferency()]);

                    if (!$stockByReferency) {
                        throw new BadRequestHttpException('Referência ' . $orderItem->getReferency() . ' não encontrada.');
                    }

                    $stock = new Stock();
                    $stock
                        ->setType(StockTypes::TYPE_REMOVE)
                        ->setQuantity($orderItem->getQuantity())
                        ->setAmount($stock->getQuantity() * $orderItem->getPrice())
                        ->setUnitPrice($orderItem->getPrice())
                        ->setCustomer(null)
                        ->setPaymentMethod($entity->getPaymentMethod())
                        ->setReferency($orderItem->getReferency())
                        ->setBrand($stockByReferency->getBrand());

                    $em->persist($stock);

                    $classMetadata = $em->getClassMetadata(Stock::class);
                    $uow->computeChangeSet($classMetadata, $stock);
                }
            }
        }
    }
}