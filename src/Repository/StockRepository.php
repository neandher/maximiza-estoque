<?php

namespace App\Repository;

use App\Entity\Stock;
use App\StockTypes;
use App\Util\Pagination;
use Doctrine\ORM\NonUniqueResultException;
use Pagerfanta\Adapter\ArrayAdapter;
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

        if (isset($routeParams['brand']) && !empty($routeParams['brand'])) {
            $qb->andWhere('stock.brand = :brand')->setParameter('brand', $routeParams['brand']);
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

    /**
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function getTotalAdd()
    {
        return $this->createQueryBuilder('stock')
            ->addSelect('SUM(stock.quantity) as totalAdd')
            ->addSelect('SUM(stock.amount) as totalAddAmount')
            ->where("stock.type = '" . StockTypes::TYPE_ADD . "'")
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function getTotalRemove()
    {
        $qb = $this->createQueryBuilder('stock');
        return $qb
            ->addSelect('SUM(stock.quantity) as totalRemove')
            ->addSelect('SUM(stock.amount) as totalRemoveAmount')
            ->where("stock.type = '" . StockTypes::TYPE_REMOVE . "'")
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function getTotal()
    {
        $qb = $this->createQueryBuilder('stock');
        return $qb
            ->addSelect('SUM(stock.quantity) as total')
            ->addSelect('SUM(stock.amount) as totalAmount')
            ->getQuery()->getOneOrNullResult();
    }

    public function balance(Pagination $pagination)
    {
        $routeParams = $pagination->getRouteParams();

        $conn = $this->getEntityManager()
            ->getConnection();

        $where = 'where id > 0';
        $whereSum = 'where referency = stk.referency';
        $having = '';

        if (isset($routeParams['search']) && !empty($routeParams['search'])) {
            $where .= ' and referency like "%' . $routeParams['search'] . '%" ';
        }

        if (isset($routeParams['brand']) && !empty($routeParams['brand'])) {
            $where .= ' and brand_id = "' . $routeParams['brand'] . '" ';
        }

        if (isset($routeParams['filter_balance']) && !empty($routeParams['filter_balance'])) {
            if ($routeParams['filter_balance'] === 'balance_positive') {
                $having .= ' having saldo > 0 ';
            }
        }

        if ((isset($routeParams['date_start']) && !empty($routeParams['date_start'])) && (isset($routeParams['date_end']) && !empty($routeParams['date_end']))) {

            $routeParams['date_start'] .= ' 00:00';
            $routeParams['date_end'] .= ' 23:59';

            $date_start = \DateTime::createFromFormat('d/m/Y H:i', $routeParams['date_start'])->format('Y-m-d H:i');
            $date_end = \DateTime::createFromFormat('d/m/Y H:i', $routeParams['date_end'])->format('Y-m-d H:i');

            if ($date_start && $date_end) {
                $whereSum .= ' and created_at >= "' . $date_start . '" and created_at <= "' . $date_end . '" ';
            }
        }

        $sql = '
                select distinct referency, (select SUM(stock.quantity) from stock ' . $whereSum . ' ) as saldo 
                from stock as stk ' . $where . '
                group by referency   
                ' . $having . '                             
                order by referency asc
            ';
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function balancePaginator(Pagination $pagination)
    {
        $routeParams = $pagination->getRouteParams();

        $paginator = new Pagerfanta(new ArrayAdapter($this->balance($pagination)));

        $paginator->setMaxPerPage($routeParams['num_items']);
        $paginator->setCurrentPage($routeParams['page']);

        return $paginator;
    }
}
