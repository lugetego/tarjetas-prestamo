<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AyudaController extends AbstractController
{
    #[Route(path: '/ayuda', name: 'app_ayuda', methods: ['GET'])]
    public function __invoke(): Response
    {
        return $this->render('ayuda/index.html.twig');
    }
}
