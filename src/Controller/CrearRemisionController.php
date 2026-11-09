<?php

namespace App\Controller;

use App\Entity\Remision;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;

class CrearRemisionController extends AbstractController
{
    #[Route(path: '/remision/nueva', name: 'app_remision_nueva', methods: ['POST'])]
    #[IsCsrfTokenValid('submit', tokenKey: '_token')]
    public function __invoke(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($user->getBiblioteca() === null) {
            $this->addFlash('error', 'Tu cuenta no pertenece a una biblioteca; no puede crear remisiones.');

            return $this->redirectToRoute('app_historial');
        }

        $nombre = trim((string) $request->request->get('nombre'));

        if ($nombre === '') {
            $this->addFlash('error', 'Dale un nombre a la remisión manual.');

            return $this->redirectToRoute('app_upload');
        }

        $remision = new Remision();
        $remision->setNombre($nombre);
        $remision->setPrefijoAdquisicion(trim((string) $request->request->get('prefijoAdquisicion', 'GM(1)')) ?: 'GM(1)');
        $remision->setArchivoOriginal(null);
        $remision->setBiblioteca($user->getBiblioteca());

        $entityManager->persist($remision);
        $entityManager->flush();

        $this->addFlash('success', sprintf('Remisión "%s" creada. Ahora puedes agregar tarjetas manualmente.', $nombre));

        return $this->redirectToRoute('app_remision_show', ['id' => $remision->getId()]);
    }
}
