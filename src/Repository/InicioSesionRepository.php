<?php

namespace App\Repository;

use App\Entity\InicioSesion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InicioSesion>
 */
class InicioSesionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InicioSesion::class);
    }

    /** @return array<int|string, int> id de biblioteca (o 'admin') => número de inicios de sesión */
    public function contarPorBiblioteca(): array
    {
        $filas = $this->createQueryBuilder('i')
            ->select('IDENTITY(u.biblioteca) AS bibliotecaId, COUNT(i.id) AS total')
            ->join('i.user', 'u')
            ->groupBy('u.biblioteca')
            ->getQuery()
            ->getArrayResult();

        $resultado = [];
        foreach ($filas as $fila) {
            $clave = $fila['bibliotecaId'] ?? 'admin';
            $resultado[$clave] = (int) $fila['total'];
        }

        return $resultado;
    }

    /** @return array<int, array{username: string, biblioteca: ?string, total: int, ultimo: \DateTimeImmutable}> */
    public function contarPorUsuario(): array
    {
        $filas = $this->createQueryBuilder('i')
            ->select('u.username AS username, b.nombre AS biblioteca, COUNT(i.id) AS total, MAX(i.fecha) AS ultimo')
            ->join('i.user', 'u')
            ->leftJoin('u.biblioteca', 'b')
            ->groupBy('u.id, u.username, b.nombre')
            ->orderBy('username', 'ASC')
            ->getQuery()
            ->getArrayResult();

        foreach ($filas as &$fila) {
            $fila['total'] = (int) $fila['total'];
            $fila['ultimo'] = new \DateTimeImmutable($fila['ultimo']);
        }

        return $filas;
    }
}
