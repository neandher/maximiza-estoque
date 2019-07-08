<?php

namespace App\Repository;

use App\Entity\Customer;
use App\Util\Pagination;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bridge\Doctrine\RegistryInterface;

class CustomerRepository extends BaseRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    public function queryLatest(Pagination $pagination)
    {
        $routeParams = $pagination->getRouteParams();

        $qb = $this->createQueryBuilder('customer')
            ->distinct(true)
            ->innerJoin('customer.state', 'state')
            ->addSelect('state')
            ->innerJoin('customer.user', 'user')
            ->addSelect('user')
            ->leftJoin('customer.customerAddresses', 'customerAddresses')
            ->addSelect('customerAddresses')
            ->leftJoin('customerAddresses.address', 'address')
            ->addSelect('address')
            ->leftJoin('address.uf', 'uf')
            ->addSelect('uf')
            ->leftJoin('customer.customerBrands', 'customerBrands')
            ->addSelect('customerBrands')
            ->leftJoin('customerBrands.brand', 'brand')
            ->addSelect('brand')
            /*->leftJoin('customer.customerObservations', 'customerObservations')
            ->addSelect('customerObservations')
            ->leftJoin('customerObservations.user', 'customerObservationUser')
            ->addSelect('customerObservationUser')*/
        ;

        if (isset($routeParams['search']) && !empty($routeParams['search'])) {
            $qb->andWhere(
                $qb->expr()->like('customer.name', ':search')
            )->setParameter('search', '%' . $routeParams['search'] . '%');
        }

        if (isset($routeParams['state']) && !empty($routeParams['state'])) {
            $qb->andWhere('state.id = :state')->setParameter('state', $routeParams['state']);
        }

        if (isset($routeParams['category']) && !empty($routeParams['category'])) {
            $qb->andWhere('category.id = :category')->setParameter('category', $routeParams['category']);
        }

        if (isset($routeParams['city']) && !empty($routeParams['city'])) {
            $qb->andWhere('address.city like :city')->setParameter('city', '%'.$routeParams['city'].'%');
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

    public function queryLatestForm()
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.category', 'category')
            ->orderBy('category.name', 'ASC')
            ->addOrderBy('c.name', 'ASC');
    }
}
