<?php

namespace App\Service;

use App\Entity\Biblioteca;
use App\Entity\Remision;
use App\Entity\Tarjeta;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class XlsxImporter
{
    /** Encabezado esperado (normalizado) => nombre de propiedad en Tarjeta */
    private const COLUMNAS = [
        'adquision' => 'setAdquision',
        'titulo' => 'setTitulo',
        'autor' => 'setAutor',
        'sistema' => 'setSistema',
        'clasificacion' => 'setClasificacion',
    ];

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * Importa un .xlsx: cada hoja no vacía se convierte en una Remision.
     *
     * @return Remision[] remisiones creadas
     */
    public function importar(string $rutaArchivo, string $nombreArchivoOriginal, Biblioteca $biblioteca): array
    {
        $spreadsheet = IOFactory::load($rutaArchivo);
        $remisiones = [];

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $tarjetas = $this->leerHoja($sheet);

            if (empty($tarjetas)) {
                continue;
            }

            $remision = new Remision();
            $remision->setNombre($sheet->getTitle());
            $remision->setArchivoOriginal($nombreArchivoOriginal);
            $remision->setBiblioteca($biblioteca);

            foreach ($tarjetas as $tarjeta) {
                $remision->addTarjeta($tarjeta);
            }

            $this->entityManager->persist($remision);
            $remisiones[] = $remision;
        }

        $this->entityManager->flush();

        return $remisiones;
    }

    /** @return Tarjeta[] */
    private function leerHoja(Worksheet $sheet): array
    {
        $filas = $sheet->toArray(null, true, true, false);

        if (empty($filas)) {
            return [];
        }

        $encabezado = array_map($this->normalizar(...), array_shift($filas));
        $indices = [];
        foreach (self::COLUMNAS as $columna => $setter) {
            $posicion = array_search($columna, $encabezado, true);
            if ($posicion !== false) {
                $indices[$columna] = $posicion;
            }
        }

        if (empty($indices)) {
            return [];
        }

        $tarjetas = [];
        foreach ($filas as $fila) {
            $valores = [];
            foreach ($indices as $columna => $posicion) {
                $valores[$columna] = isset($fila[$posicion]) ? trim((string) $fila[$posicion]) : '';
            }

            if (empty(array_filter($valores))) {
                continue; // fila en blanco
            }

            $tarjeta = new Tarjeta();
            foreach ($valores as $columna => $valor) {
                $setter = self::COLUMNAS[$columna];
                $tarjeta->$setter($valor);
            }

            $tarjetas[] = $tarjeta;
        }

        return $tarjetas;
    }

    private function normalizar(mixed $valor): string
    {
        $texto = trim((string) $valor);
        $texto = mb_strtolower($texto);
        $sinAcentos = strtr($texto, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
        ]);

        return $sinAcentos;
    }
}
