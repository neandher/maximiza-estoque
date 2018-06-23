<?php

namespace App\Repository;

use App\Entity\Stock;
use App\Util\Pagination;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bridge\Doctrine\RegistryInterface;

class StockRepository extends BaseRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Stock::class);
    }

    public function queryLatest(Pagination $pagination)
    {
        $routeParams = $pagination->getRouteParams();

        $qb = $this->createQueryBuilder('stock');

        if (isset($routeParams['search']) && !empty($routeParams['search'])) {
            $qb->andWhere('stock.referency like :search')->setParameter('search', '%' . $routeParams['search'] . '%');
        }

        if (isset($routeParams['type']) && !empty($routeParams['type'])) {
            $qb->andWhere('stock.type = :type')->setParameter('type', $routeParams['type']);
        }

        if (isset($routeParams['user']) && !empty($routeParams['user'])) {
            $qb->andWhere('stock.user = :user')->setParameter('user', $routeParams['user']);
        }

        if ((isset($routeParams['date_start']) && !empty($routeParams['date_start'])) && (isset($routeParams['date_end']) && !empty($routeParams['date_end']))) {

            $routeParams['date_start'] .= ' 00:00';
            $routeParams['date_end'] .= ' 23:59';

            $date_start = \DateTime::createFromFormat('d/m/Y H:i', $routeParams['date_start'])->format('Y-m-d H:i');
            $date_end = \DateTime::createFromFormat('d/m/Y H:i', $routeParams['date_end'])->format('Y-m-d H:i');

            if ($date_start && $date_end) {
                $qb->andWhere('stock.createdAt >= :date_start')->setParameter('date_start', $date_start);
                $qb->andWhere('stock.createdAt <= :date_end')->setParameter('date_end', $date_end);
            }
        }

        $qb = $this->addOrderingQueryBuilder($qb, $routeParams);

        return $qb->getQuery();
    }

    public function findLatest(Pagination $pagination)
    {
        $routeParams = $pagination->getRouteParams();

        $paginator = new Pagerfanta(new DoctrineORMAdapter($this->queryLatest($pagination), false));

        $paginator->setMaxPerPage($routeParams['num_items']);
        $paginator->setCurrentPage($routeParams['page']);

        return $paginator;
    }
}
