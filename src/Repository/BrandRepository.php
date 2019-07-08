<?php

namespace App\Repository;

use App\Entity\Brand;
use App\Util\Pagination;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bridge\Doctrine\RegistryInterface;

class BrandRepository extends BaseRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Brand::class);
    }

    public function queryLatest(Pagination $pagination)
    {
        $routeParams = $pagination->getRouteParams();

        $qb = $this->createQueryBuilder('brand');

        if (isset($routeParams['search']) && !empty($routeParams['search'])) {
            $qb->andWhere(
                $qb->expr()->like('brand.name', ':search')
            )->setParameter('search', '%' . $routeParams['search'] . '%');
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
        return $this->createQueryBuilder('brand')
            ->orderBy('brand.name', 'ASC');
    }
}
