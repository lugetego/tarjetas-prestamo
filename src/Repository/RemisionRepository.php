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

    public const POR_PAGINA = 10;

    /** @return Remision[] */
    public function findByBibliotecaOrderedByFecha(Biblioteca $biblioteca, int $pagina = 1): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.biblioteca = :biblioteca')
            ->setParameter('biblioteca', $biblioteca)
            ->orderBy('r.creadoEn', 'DESC')
            ->setFirstResult((max($pagina, 1) - 1) * self::POR_PAGINA)
            ->setMaxResults(self::POR_PAGINA)
            ->getQuery()
            ->getResult();
    }

    public function contarPorBiblioteca(Biblioteca $biblioteca): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.biblioteca = :biblioteca')
            ->setParameter('biblioteca', $biblioteca)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** @return Remision[] */
    public function findAllOrderedByFecha(int $pagina = 1): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.creadoEn', 'DESC')
            ->setFirstResult((max($pagina, 1) - 1) * self::POR_PAGINA)
            ->setMaxResults(self::POR_PAGINA)
            ->getQuery()
            ->getResult();
    }

    public function contarTodas(): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->getQuery()
            ->getSingleScalarResult();
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
