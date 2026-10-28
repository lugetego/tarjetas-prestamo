<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\RemisionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UploadResultadoController extends AbstractController
{
    #[Route(path: '/subida/resultado', name: 'app_upload_resultado', methods: ['GET'])]
    public function __invoke(Request $request, RemisionRepository $remisionRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $ids = array_filter(array_map('intval', explode(',', (string) $request->query->get('ids', ''))));

        $remisiones = $ids ? $remisionRepository->findBy(['id' => $ids, 'biblioteca' => $user->getBiblioteca()]) : [];

        return $this->render('upload/resultado.html.twig', [
            'remisiones' => $remisiones,
        ]);
    }
}
