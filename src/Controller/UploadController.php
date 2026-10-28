<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\XlsxImporter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class UploadController extends AbstractController
{
    private const EXTENSION_PERMITIDA = 'xlsx';
    private const TAMANO_MAXIMO_BYTES = 10 * 1024 * 1024; // 10 MB

    #[Route(path: '/', name: 'app_upload', methods: ['GET', 'POST'])]
    public function __invoke(
        Request $request,
        XlsxImporter $importer,
        CsrfTokenManagerInterface $csrfTokenManager,
        #[Autowire('%kernel.project_dir%')] string $projectDir,
    ): Response {
        if ($request->isMethod('POST')) {
            if (!$csrfTokenManager->isTokenValid(new CsrfToken('submit', $request->request->get('_token')))) {
                throw $this->createAccessDeniedException('Token CSRF inválido.');
            }

            $archivo = $request->files->get('archivo');

            if (!$archivo) {
                $this->addFlash('error', 'Selecciona un archivo .xlsx.');

                return $this->redirectToRoute('app_upload');
            }

            if (strtolower($archivo->getClientOriginalExtension()) !== self::EXTENSION_PERMITIDA) {
                $this->addFlash('error', 'Solo se aceptan archivos .xlsx.');

                return $this->redirectToRoute('app_upload');
            }

            if ($archivo->getSize() > self::TAMANO_MAXIMO_BYTES) {
                $this->addFlash('error', 'El archivo supera el tamaño máximo permitido (10 MB).');

                return $this->redirectToRoute('app_upload');
            }

            try {
                /** @var User $user */
                $user = $this->getUser();

                $nombreOriginal = $archivo->getClientOriginalName();
                $nombreGuardado = sprintf('%s-%s', uniqid(), preg_replace('/[^A-Za-z0-9_.-]+/', '-', $nombreOriginal));
                $archivo->move($projectDir.'/storage/uploads', $nombreGuardado);

                $remisiones = $importer->importar($projectDir.'/storage/uploads/'.$nombreGuardado, $nombreOriginal, $user->getBiblioteca());
            } catch (FileException|\Throwable $e) {
                $this->addFlash('error', 'No se pudo leer el archivo: '.$e->getMessage());

                return $this->redirectToRoute('app_upload');
            }

            if (empty($remisiones)) {
                $this->addFlash('error', 'El archivo no tiene hojas con datos reconocibles (adquision, Título, Autor, sistema, Clasificacion).');

                return $this->redirectToRoute('app_upload');
            }

            $this->addFlash('success', sprintf('Se importaron %d remisión(es) con un total de %d tarjeta(s).', count($remisiones), array_sum(array_map(fn ($r) => $r->getTarjetas()->count(), $remisiones))));

            return $this->redirectToRoute('app_upload_resultado', [
                'ids' => implode(',', array_map(fn ($r) => $r->getId(), $remisiones)),
            ]);
        }

        return $this->render('upload/index.html.twig');
    }
}
