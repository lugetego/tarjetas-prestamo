<?php

namespace App\Controller;

use App\Entity\Remision;
use App\Entity\Tarjeta;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AgregarTarjetaController extends AbstractController
{
    #[Route(path: '/remision/{id}/tarjeta', name: 'app_tarjeta_agregar', methods: ['POST'])]
    #[IsGranted('ACCESS', subject: 'remision')]
    #[IsCsrfTokenValid('submit', tokenKey: '_token')]
    public function __invoke(Remision $remision, Request $request, EntityManagerInterface $entityManager): Response
    {
        $tarjeta = new Tarjeta();
        $tarjeta->setClasificacion(trim((string) $request->request->get('clasificacion')));
        $tarjeta->setAdquision(trim((string) $request->request->get('adquision')));
        $tarjeta->setAutor(trim((string) $request->request->get('autor')));
        $tarjeta->setTitulo(trim((string) $request->request->get('titulo')));
        $tarjeta->setSistema(trim((string) $request->request->get('sistema')));

        $remision->addTarjeta($tarjeta);

        $entityManager->persist($tarjeta);
        $entityManager->flush();

        $this->addFlash('success', 'Tarjeta agregada.');

        return $this->redirectToRoute('app_remision_show', ['id' => $remision->getId()]);
    }
}
