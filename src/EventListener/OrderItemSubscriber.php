<?php

namespace App\EventListener;

use App\Entity\Order;
use App\Entity\OrderItems;
use App\Entity\Stock;
use App\StockTypes;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;

class OrderItemSubscriber implements EventSubscriber
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

        foreach ($uow->getScheduledEntityDeletions() as $keyEntity => $entity) {
            if ($entity instanceof OrderItems) {

                $stock = $this->handleStock($em, $entity, StockTypes::TYPE_ADD);
                $em->persist($stock);

                $classMetadata = $em->getClassMetadata(Stock::class);
                $uow->computeChangeSet($classMetadata, $stock);
            }
        }

        foreach ($uow->getScheduledEntityUpdates() as $keyEntity => $entity) {
            if ($entity instanceof OrderItems) {
                foreach ($uow->getEntityChangeSet($entity) as $keyField => $field) {
                    if ($keyField === 'quantity') {
                        $quantityOld = $field[0];

                        if ($entity->getQuantity() < $quantityOld) {
                            $quantity = $quantityOld - $entity->getQuantity();
                            $stock = $this->handleStock($em, $entity, StockTypes::TYPE_ADD, $quantity);
                        }

                        if ($entity->getQuantity() > $quantityOld) {
                            $quantity = $entity->getQuantity() - $quantityOld;
                            $stock = $this->handleStock($em, $entity, StockTypes::TYPE_REMOVE, $quantity);
                        }

                        $em->persist($stock);

                        $classMetadata = $em->getClassMetadata(Stock::class);
                        $uow->computeChangeSet($classMetadata, $stock);
                    }
                }
            }
        }
    }

    private function handleStock(\Doctrine\ORM\EntityManager $em, OrderItems $entity, $type, $quantity = 0): Stock
    {
        $stockByReferency = $em->getRepository(Stock::class)->findOneBy(['referency' => $entity->getReferency()]);

        $stock = new Stock();
        $stock
            ->setType($type)
            ->setQuantity($quantity > 0 ? $quantity : $entity->getQuantity())
            ->setAmount($stock->getQuantity() * $entity->getPrice())
            ->setUnitPrice($entity->getPrice())
            ->setCustomer(null)
            ->setPaymentMethod(null)
            ->setReferency($entity->getReferency())
            ->setBrand($stockByReferency->getBrand())
            ->setObs('Estoque adicionado automaticamente por cancelamento de venda');

        return $stock;
    }
}