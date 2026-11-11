<?php

namespace App\Controller;

use App\Repository\BibliotecaRepository;
use App\Repository\InicioSesionRepository;
use App\Repository\RemisionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ReporteController extends AbstractController
{
    #[Route(path: '/reportes', name: 'app_reportes', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function __invoke(
        BibliotecaRepository $bibliotecaRepository,
        RemisionRepository $remisionRepository,
        InicioSesionRepository $inicioSesionRepository,
    ): Response {
        $usoPorBiblioteca = [];
        foreach ($remisionRepository->contarUsoPorBiblioteca() as $fila) {
            $usoPorBiblioteca[$fila['bibliotecaId']] = $fila;
        }

        $inicioSesionPorBiblioteca = $inicioSesionRepository->contarPorBiblioteca();

        $filas = [];
        foreach ($bibliotecaRepository->findAll() as $biblioteca) {
            $uso = $usoPorBiblioteca[$biblioteca->getId()] ?? null;
            $filas[] = [
                'nombre' => $biblioteca->getNombre(),
                'numRemisiones' => $uso['numRemisiones'] ?? 0,
                'numTarjetas' => $uso['numTarjetas'] ?? 0,
                'numInicios' => $inicioSesionPorBiblioteca[$biblioteca->getId()] ?? 0,
            ];
        }
        usort($filas, fn (array $a, array $b) => $a['nombre'] <=> $b['nombre']);

        return $this->render('reporte/index.html.twig', [
            'filas' => $filas,
            'numInicios_admin' => $inicioSesionPorBiblioteca['admin'] ?? 0,
            'usuarios' => $inicioSesionRepository->contarPorUsuario(),
        ]);
    }
}
