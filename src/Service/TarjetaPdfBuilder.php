<?php

namespace App\Service;

use App\Entity\Biblioteca;
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
        $biblioteca = $remision->getBiblioteca();
        $anchoPt = $biblioteca->getAnchoTarjetaPt();
        $altoPt = $biblioteca->getAltoTarjetaPt();
        $escalaY = $altoPt / Biblioteca::ALTO_TARJETA_PT_DEFAULT;

        $html = $this->twig->render('tarjeta/pdf.html.twig', [
            'remision' => $remision,
            'tarjetas' => $remision->getTarjetas(),
            'anchoPt' => $anchoPt,
            'altoPt' => $altoPt,
            'escalaX' => $anchoPt / Biblioteca::ANCHO_TARJETA_PT_DEFAULT,
            'escalaY' => $escalaY,
            'autorMarginTopPt' => $biblioteca->getAutorMarginTopPt() ?? round(22 * $escalaY, 1),
            'tituloMarginTopPt' => $biblioteca->getTituloMarginTopPt() ?? round(12 * $escalaY, 1),
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
