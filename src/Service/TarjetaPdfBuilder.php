<?php

namespace App\Service;

use App\Entity\Remision;
use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;

class TarjetaPdfBuilder
{
    public function __construct(private readonly Environment $twig)
    {
    }

    public function generar(Remision $remision): string
    {
        $html = $this->twig->render('tarjeta/pdf.html.twig', [
            'remision' => $remision,
            'tarjetas' => $remision->getTarjetas(),
        ]);

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'Times New Roman');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->render();

        return $dompdf->output();
    }
}
