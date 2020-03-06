<?php

namespace App\Repository;

use App\Entity\Bill;
use App\Util\Pagination;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bridge\Doctrine\RegistryInterface;

class BillRepository extends BaseRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Bill::class);
    }

    protected function queryLatest(Pagination $pagination)
    {
        $routeParams = $pagination->getRouteParams();

        $qb = $this->createQueryBuilder('bill')
            ->innerJoin('bill.billPlan', 'billPlan')
            ->addSelect('billPlan')
            ->innerJoin('billPlan.billPlanCategory', 'billPlanCategory')
            ->addSelect('billPlanCategory')
            ->groupBy('bill.id');

        $qb = $this->filters($qb, $routeParams);

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

    private function filters(QueryBuilder $qb, $routeParams = [])
    {
        if (!empty($routeParams['search'])) {
            $qb->andWhere('bill.description LIKE :search')->setParameter('search', '%' . $routeParams['search'] . '%');
        }

        if (!empty($routeParams['type'])) {
            $qb->andWhere('bill.type = :type')->setParameter('type', $routeParams['type']);
        }

        if (!empty($routeParams['status'])) {
            $qb->andWhere('bill.status = :status')->setParameter('status', $routeParams['status']);
        }

        if (isset($routeParams['user']) && !empty($routeParams['user'])) {
            $qb->andWhere('bill.user = :user')->setParameter('user', $routeParams['user']);
        }

        if (!empty($routeParams['bill_status_desc'])) {

            if ($routeParams['bill_status_desc'] == Bill::BILL_STATUS_PAID) {

                $qb->andWhere('bill.amountPaid is not null');

                if (!empty($routeParams['billYear'])) {
                    $qb->andWhere('year(bill.paymentDate) = :year')->setParameter(':year', $routeParams['billYear']);
                }

                if (!empty($routeParams['billMonth'])) {
                    $qb->andWhere('month(bill.paymentDate) = :month')->setParameter(':month', $routeParams['billMonth']);
                }
            }

            if ($routeParams['bill_status_desc'] == Bill::BILL_STATUS_OPEN) {

                $qb->andWhere('bill.amountPaid is null');

                if (!empty($routeParams['billYear'])) {
                    $qb->andWhere('year(bill.dueDate) = :year')->setParameter(':year', $routeParams['billYear']);
                }

                if (!empty($routeParams['billMonth'])) {
                    $qb->andWhere('month(bill.dueDate) = :month')->setParameter(':month', $routeParams['billMonth']);
                }
            }
        }

        if (!empty($routeParams['date_start']) && !empty($routeParams['date_end'])) {

            $date_start = \DateTime::createFromFormat('d/m/Y', $routeParams['date_start'])->format('Y-m-d');
            $date_end = \DateTime::createFromFormat('d/m/Y', $routeParams['date_end'])->format('Y-m-d');

            $qb->andWhere('bill.dueDate >= :date_start')->setParameter('date_start', $date_start);
            $qb->andWhere('bill.dueDate <= :date_end')->setParameter('date_end', $date_end);
        }

        if (!empty($routeParams['overdue'])) {
            $qb->andWhere('bill.dueDate <= :now')->setParameter('now', new \DateTime())
                ->andWhere('bill.amountPaid IS NULL');
        }

        if (!empty($routeParams['sum_amount']) && $routeParams['sum_amount'] === true) {
            $qb->select("SUM(CAST( replace( replace( bill.amount,'.','' ),',','.' )  AS DECIMAL( 13,2 ) )) as amountTotal");
        }

        if (!empty($routeParams['sum_amount_paid']) && $routeParams['sum_amount_paid'] === true) {
            $qb->select("SUM(CAST( replace( replace( bill.amountPaid,'.','' ),',','.' )  AS DECIMAL( 13,2 ) )) as amountPaidTotal");
        }

        return $qb;
    }

    /**
     * @param $id
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function findOneById($id)
    {
        return $this->createQueryBuilder('bill')
            ->innerJoin('bill.billPlan', 'billPlan')
            ->addSelect('billPlan')
            ->innerJoin('billPlan.billPlanCategory', 'billPlanCategory')
            ->addSelect('billPlanCategory')
            ->where('bill.id = :id')->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
