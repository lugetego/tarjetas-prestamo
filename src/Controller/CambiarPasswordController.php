<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;

class CambiarPasswordController extends AbstractController
{
    private const LONGITUD_MINIMA = 8;

    #[Route(path: '/cuenta/password', name: 'app_cambiar_password', methods: ['GET'])]
    public function formulario(): Response
    {
        return $this->render('cuenta/password.html.twig');
    }

    #[Route(path: '/cuenta/password', name: 'app_cambiar_password_submit', methods: ['POST'])]
    #[IsCsrfTokenValid('submit', tokenKey: '_token')]
    public function cambiar(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $actual = (string) $request->request->get('password_actual');
        $nueva = (string) $request->request->get('password_nueva');
        $confirmacion = (string) $request->request->get('password_confirmacion');

        if (!$passwordHasher->isPasswordValid($user, $actual)) {
            $this->addFlash('error', 'La contraseña actual no es correcta.');

            return $this->redirectToRoute('app_cambiar_password');
        }

        if (mb_strlen($nueva) < self::LONGITUD_MINIMA) {
            $this->addFlash('error', sprintf('La nueva contraseña debe tener al menos %d caracteres.', self::LONGITUD_MINIMA));

            return $this->redirectToRoute('app_cambiar_password');
        }

        if ($nueva !== $confirmacion) {
            $this->addFlash('error', 'La nueva contraseña y su confirmación no coinciden.');

            return $this->redirectToRoute('app_cambiar_password');
        }

        $user->setPassword($passwordHasher->hashPassword($user, $nueva));
        $entityManager->flush();

        $this->addFlash('success', 'Tu contraseña se actualizó correctamente.');

        return $this->redirectToRoute('app_historial');
    }
}
