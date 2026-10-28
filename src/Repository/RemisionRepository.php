<?php

namespace App\Repository;

use App\Entity\Biblioteca;
use App\Entity\Remision;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Remision>
 */
class RemisionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Remision::class);
    }

    /** @return Remision[] */
    public function findByBibliotecaOrderedByFecha(Biblioteca $biblioteca): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.biblioteca = :biblioteca')
            ->setParameter('biblioteca', $biblioteca)
            ->orderBy('r.creadoEn', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
