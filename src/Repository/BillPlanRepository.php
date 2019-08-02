<?php

namespace App\Repository;

use App\Entity\BillPlan;
use App\Util\Pagination;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bridge\Doctrine\RegistryInterface;

class BillPlanRepository extends BaseRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, BillPlan::class);
    }

    protected function queryLatest(Pagination $pagination)
    {
        $routeParams = $pagination->getRouteParams();

        $qb = $this->createQueryBuilder('billPlan')
            ->innerJoin('billPlan.billPlanCategory', 'billPlanCategory')
            ->addSelect('billPlanCategory');

        if (isset($routeParams['search'])) {
            $qb->andWhere('billPlan.description LIKE :search')->setParameter('search', '%' . $routeParams['search'] . '%');
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

    public function queryLatestForm($billType)
    {
        return $this->createQueryBuilder('billPlan')
            ->innerJoin('billPlan.billPlanCategory', 'billPlanCategory')
            ->addSelect('billPlanCategory')
            ->where('billPlanCategory.billType = :bill_type')->setParameter('bill_type', $billType)
            ->orderBy('billPlanCategory.description', 'ASC')
            ->addOrderBy('billPlan.description', 'ASC');
    }

    public function listBillPlans()
    {
        return $this->createQueryBuilder('billPlan')
            ->innerJoin('billPlan.billPlanCategory', 'billPlanCategory')
            ->addSelect('billPlanCategory')
            ->innerJoin('billPlanCategory.billCategory', 'billCategory')
            ->addSelect('billCategory')
            ->orderBy('billCategory.description', 'ASC')
            ->addOrderBy('billPlanCategory.description', 'ASC')
            ->addOrderBy('billPlan.description', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
