<?php

namespace App\Controller;

use App\Entity\Remision;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class EliminarRemisionController extends AbstractController
{
    #[Route(path: '/remision/{id}/eliminar', name: 'app_remision_eliminar', methods: ['POST'])]
    #[IsCsrfTokenValid('submit', tokenKey: '_token')]
    #[IsGranted('ACCESS', subject: 'remision')]
    public function __invoke(Remision $remision, EntityManagerInterface $entityManager): Response
    {
        $nombre = $remision->getNombre();

        $entityManager->remove($remision);
        $entityManager->flush();

        $this->addFlash('success', sprintf('Remisión "%s" eliminada.', $nombre));

        return $this->redirectToRoute('app_historial');
    }
}
