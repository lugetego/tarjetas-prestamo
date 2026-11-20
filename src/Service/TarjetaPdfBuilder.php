<?php

namespace App\Service;

use App\Entity\Biblioteca;
use App\Entity\Remision;
use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;

class TarjetaPdfBuilder
{
    /** Tamaños de fuente (pt) a probar para el autor, del más grande al más chico. */
    private const TAMANOS_FUENTE_AUTOR = [10.0, 9.0, 8.0, 7.0];

    /** Máximo de líneas que debe ocupar el autor antes de reducir la fuente. */
    private const MAX_LINEAS_AUTOR = 2;

    /** Ancho promedio estimado de un carácter en negritas, como fracción del tamaño de fuente. */
    private const FACTOR_ANCHO_CARACTER = 0.5;

    public function __construct(private readonly Environment $twig)
    {
    }

    public function generar(Remision $remision): string
    {
        $biblioteca = $remision->getBiblioteca();
        $anchoPt = $biblioteca->getAnchoTarjetaPt();
        $altoPt = $biblioteca->getAltoTarjetaPt();
        $escalaX = $anchoPt / Biblioteca::ANCHO_TARJETA_PT_DEFAULT;
        $escalaY = $altoPt / Biblioteca::ALTO_TARJETA_PT_DEFAULT;
        $anchoAutorPt = round(179 * $escalaX, 1);

        $autorFontSizes = [];
        foreach ($remision->getTarjetas() as $tarjeta) {
            $autorFontSizes[$tarjeta->getId()] = $this->calcularFontSizeAutor($tarjeta->getAutor(), $anchoAutorPt);
        }

        $html = $this->twig->render('tarjeta/pdf.html.twig', [
            'remision' => $remision,
            'tarjetas' => $remision->getTarjetas(),
            'anchoPt' => $anchoPt,
            'altoPt' => $altoPt,
            'escalaX' => $escalaX,
            'escalaY' => $escalaY,
            'anchoAutorPt' => $anchoAutorPt,
            'autorFontSizes' => $autorFontSizes,
            'autorMarginTopPt' => $biblioteca->getAutorMarginTopPt() ?? round(22 * $escalaY, 1),
            'tituloMarginTopPt' => $biblioteca->getTituloMarginTopPt() ?? round(12 * $escalaY, 1),
            'derechaAnchoPt' => round(90 * $escalaX, 1) + ($biblioteca->getPrefijoDesplazamientoXPt() ?? 0.0),
        ]);

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'Times New Roman');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->render();

        return $dompdf->output();
    }

    private function calcularFontSizeAutor(string $autor, float $anchoDisponiblePt): float
    {
        $longitud = mb_strlen($autor);

        foreach (self::TAMANOS_FUENTE_AUTOR as $tamano) {
            $caracteresPorLinea = (int) floor($anchoDisponiblePt / (self::FACTOR_ANCHO_CARACTER * $tamano));
            if ($caracteresPorLinea > 0 && $longitud <= $caracteresPorLinea * self::MAX_LINEAS_AUTOR) {
                return $tamano;
            }
        }

        return min(self::TAMANOS_FUENTE_AUTOR);
    }
}
