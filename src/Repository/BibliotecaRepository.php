<?php

namespace App\Repository;

use App\Entity\Biblioteca;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Biblioteca>
 */
class BibliotecaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Biblioteca::class);
    }

    public function findOneByNombreInsensible(string $nombre): ?Biblioteca
    {
        return $this->createQueryBuilder('b')
            ->where('LOWER(b.nombre) = LOWER(:nombre)')
            ->setParameter('nombre', trim($nombre))
            ->getQuery()
            ->getOneOrNullResult();
    }
}
