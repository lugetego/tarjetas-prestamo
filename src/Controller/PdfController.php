<?php

namespace App\Controller;

use App\Entity\Remision;
use App\Service\TarjetaPdfBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PdfController extends AbstractController
{
    #[Route(path: '/remision/{id}/pdf', name: 'app_remision_pdf', methods: ['GET'])]
    #[IsGranted('ACCESS', subject: 'remision')]
    public function __invoke(Remision $remision, TarjetaPdfBuilder $pdfBuilder): Response
    {
        $pdf = $pdfBuilder->generar($remision);

        $nombreArchivo = sprintf('tarjetas-%s.pdf', preg_replace('/[^A-Za-z0-9_-]+/', '-', $remision->getNombre()));

        return new Response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf('inline; filename="%s"', $nombreArchivo),
        ]);
    }
}
