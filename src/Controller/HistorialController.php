<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\RemisionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HistorialController extends AbstractController
{
    #[Route(path: '/historial', name: 'app_historial', methods: ['GET'])]
    public function __invoke(Request $request, RemisionRepository $remisionRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $total = $user->isAdmin()
            ? $remisionRepository->contarTodas()
            : $remisionRepository->contarPorBiblioteca($user->getBiblioteca());
        $totalPaginas = max(1, (int) ceil($total / RemisionRepository::POR_PAGINA));

        $pagina = min($totalPaginas, max(1, $request->query->getInt('pagina', 1)));

        $remisiones = $user->isAdmin()
            ? $remisionRepository->findAllOrderedByFecha($pagina)
            : $remisionRepository->findByBibliotecaOrderedByFecha($user->getBiblioteca(), $pagina);

        return $this->render('historial/index.html.twig', [
            'remisiones' => $remisiones,
            'pagina' => $pagina,
            'totalPaginas' => $totalPaginas,
        ]);
    }
}
