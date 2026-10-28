<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\RemisionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HistorialController extends AbstractController
{
    #[Route(path: '/historial', name: 'app_historial', methods: ['GET'])]
    public function __invoke(RemisionRepository $remisionRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('historial/index.html.twig', [
            'remisiones' => $remisionRepository->findByBibliotecaOrderedByFecha($user->getBiblioteca()),
        ]);
    }
}
