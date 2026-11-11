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

    /** @return Remision[] */
    public function findAllOrderedByFecha(): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.creadoEn', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** @return array<int, array{bibliotecaId: int, numRemisiones: int, numTarjetas: int}> */
    public function contarUsoPorBiblioteca(): array
    {
        $filas = $this->createQueryBuilder('r')
            ->select('IDENTITY(r.biblioteca) AS bibliotecaId, COUNT(DISTINCT r.id) AS numRemisiones, COUNT(t.id) AS numTarjetas')
            ->leftJoin('r.tarjetas', 't')
            ->groupBy('r.biblioteca')
            ->getQuery()
            ->getArrayResult();

        foreach ($filas as &$fila) {
            $fila['numRemisiones'] = (int) $fila['numRemisiones'];
            $fila['numTarjetas'] = (int) $fila['numTarjetas'];
        }

        return $filas;
    }
}
