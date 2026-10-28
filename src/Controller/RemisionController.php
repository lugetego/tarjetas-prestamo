<?php

namespace App\Controller;

use App\Entity\Remision;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class RemisionController extends AbstractController
{
    #[Route(path: '/remision/{id}', name: 'app_remision_show', methods: ['GET', 'POST'])]
    #[IsGranted('ACCESS', subject: 'remision')]
    public function __invoke(Remision $remision, Request $request, EntityManagerInterface $entityManager, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        if ($request->isMethod('POST')) {
            if (!$csrfTokenManager->isTokenValid(new CsrfToken('submit', $request->request->get('_token')))) {
                throw $this->createAccessDeniedException('Token CSRF inválido.');
            }

            $remision->setPrefijoAdquisicion((string) $request->request->get('prefijoAdquisicion', $remision->getPrefijoAdquisicion()));

            $datosTarjetas = $request->request->all('tarjetas');
            $eliminar = $request->request->all('eliminar');

            foreach ($remision->getTarjetas() as $tarjeta) {
                $id = (string) $tarjeta->getId();

                if (in_array($id, $eliminar, true)) {
                    $remision->getTarjetas()->removeElement($tarjeta);
                    $entityManager->remove($tarjeta);
                    continue;
                }

                if (isset($datosTarjetas[$id])) {
                    $datos = $datosTarjetas[$id];
                    $tarjeta->setClasificacion(trim($datos['clasificacion'] ?? ''));
                    $tarjeta->setAdquision(trim($datos['adquision'] ?? ''));
                    $tarjeta->setAutor(trim($datos['autor'] ?? ''));
                    $tarjeta->setTitulo(trim($datos['titulo'] ?? ''));
                    $tarjeta->setSistema(trim($datos['sistema'] ?? ''));
                }
            }

            $entityManager->flush();
            $this->addFlash('success', 'Cambios guardados.');

            return $this->redirectToRoute('app_remision_show', ['id' => $remision->getId()]);
        }

        return $this->render('remision/show.html.twig', [
            'remision' => $remision,
        ]);
    }
}
