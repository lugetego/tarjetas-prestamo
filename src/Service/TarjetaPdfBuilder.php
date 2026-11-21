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

    /** Altura de línea del autor, como múltiplo de su tamaño de fuente (debe coincidir con el CSS). */
    private const ALTURA_LINEA_AUTOR = 1.15;

    /**
     * Margen de seguridad (pt, a escala de referencia) para que el prefijo+adquisición nunca rebase
     * el borde de la tarjeta. Más grande que un simple margen de diseño: muchas impresoras no
     * imprimen hasta el borde exacto del papel, así que deja también colchón para ese margen
     * no imprimible del lado del hardware.
     */
    private const MARGEN_SEGURIDAD_DERECHA_PT = 10.0;

    /**
     * Igual que MARGEN_SEGURIDAD_DERECHA_PT pero para el borde inferior: evita que el número de
     * sistema quede tan cerca del borde que la impresora lo recorte.
     */
    private const MARGEN_SEGURIDAD_INFERIOR_PT = 10.0;

    /** Tamaño de fuente (pt) del número de sistema, a escala de referencia (debe coincidir con el CSS). */
    private const TAMANO_FUENTE_SISTEMA = 8.0;

    /** Altura de línea del número de sistema, como múltiplo de su tamaño de fuente. */
    private const ALTURA_LINEA_SISTEMA = 1.15;

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

        $autorMarginTopBase = $biblioteca->getAutorMarginTopPt() ?? round(22 * $escalaY, 1);

        $autorFontSizes = [];
        $autorMarginTops = [];
        foreach ($remision->getTarjetas() as $tarjeta) {
            $fontSize = $this->calcularFontSizeAutor($tarjeta->getAutor(), $anchoAutorPt);
            $autorFontSizes[$tarjeta->getId()] = $fontSize;
            $autorMarginTops[$tarjeta->getId()] = $this->calcularMargenTopAutor($tarjeta->getAutor(), $anchoAutorPt, $fontSize, $autorMarginTopBase);
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
            'autorMarginTops' => $autorMarginTops,
            'tituloMarginTopPt' => $biblioteca->getTituloMarginTopPt() ?? round(12 * $escalaY, 1),
            'derechaAnchoPt' => $this->calcularAnchoDerecha($biblioteca->getPrefijoDesplazamientoXPt() ?? 0.0, $escalaX),
            'sistemaTopPt' => $this->calcularTopSistema($biblioteca->getSistemaTopPt() ?? round(338.5 * $escalaY, 1), $altoPt, $escalaY),
        ]);

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'Times New Roman');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * Ancho de la caja de prefijo+adquisición (derecha, alineada a la derecha), aplicando el
     * desplazamiento configurado pero sin dejar que su borde derecho rebase el de la tarjeta.
     */
    private function calcularAnchoDerecha(float $desplazamientoPt, float $escalaX): float
    {
        $anchoBase = 90 * $escalaX;
        $anchoMaximo = (107 * $escalaX) - self::MARGEN_SEGURIDAD_DERECHA_PT;

        return round(min($anchoBase + $desplazamientoPt, $anchoMaximo), 1);
    }

    /**
     * Posición vertical del número de sistema, aplicando el valor configurado pero sin dejar que
     * su línea rebase el borde inferior de la tarjeta.
     */
    private function calcularTopSistema(float $topPt, float $altoPt, float $escalaY): float
    {
        $alturaLineaPt = self::TAMANO_FUENTE_SISTEMA * $escalaY * self::ALTURA_LINEA_SISTEMA;
        $topMaximo = $altoPt - self::MARGEN_SEGURIDAD_INFERIOR_PT - $alturaLineaPt;

        return round(min($topPt, $topMaximo), 1);
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

    /**
     * Cuántas líneas ocupará el autor con el tamaño de fuente ya elegido.
     */
    private function calcularLineasAutor(string $autor, float $anchoDisponiblePt, float $tamanoFuente): int
    {
        $longitud = mb_strlen($autor);
        $caracteresPorLinea = (int) floor($anchoDisponiblePt / (self::FACTOR_ANCHO_CARACTER * $tamanoFuente));

        if ($caracteresPorLinea <= 0) {
            return self::MAX_LINEAS_AUTOR;
        }

        return min(self::MAX_LINEAS_AUTOR, max(1, (int) ceil($longitud / $caracteresPorLinea)));
    }

    /**
     * Margen superior del autor, recorrido hacia arriba cuando ocupa más de una línea, para que
     * su última línea siempre termine en la misma posición que un autor de una sola línea — y así
     * no invada la línea impresa que separa AUTOR de TÍTULO.
     */
    private function calcularMargenTopAutor(string $autor, float $anchoDisponiblePt, float $tamanoFuente, float $margenBase): float
    {
        $lineas = $this->calcularLineasAutor($autor, $anchoDisponiblePt, $tamanoFuente);
        $alturaLineaPt = $tamanoFuente * self::ALTURA_LINEA_AUTOR;

        return round(max(0.0, $margenBase - ($lineas - 1) * $alturaLineaPt), 1);
    }
}
