<?php

namespace App\Command;

use App\Repository\BibliotecaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:configurar-tamano-tarjeta', description: 'Define el tamaño físico de tarjeta (en cm) que usa una biblioteca, o lo restablece al estándar de 3x5" si se omiten ancho/alto')]
class ConfigurarTamanoTarjetaCommand extends Command
{
    private const PT_POR_CM = 28.3465;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly BibliotecaRepository $bibliotecaRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('biblioteca', InputArgument::REQUIRED, 'Nombre de la biblioteca')
            ->addArgument('anchoCm', InputArgument::OPTIONAL, 'Ancho de la tarjeta en cm (el lado corto)')
            ->addArgument('altoCm', InputArgument::OPTIONAL, 'Alto de la tarjeta en cm (el lado largo)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $nombreBiblioteca = trim((string) $input->getArgument('biblioteca'));

        $biblioteca = $this->bibliotecaRepository->findOneByNombreInsensible($nombreBiblioteca);
        if ($biblioteca === null) {
            $io->error(sprintf('No existe ninguna biblioteca con el nombre "%s".', $nombreBiblioteca));

            return Command::FAILURE;
        }

        $anchoCm = $input->getArgument('anchoCm');
        $altoCm = $input->getArgument('altoCm');

        if ($anchoCm === null || $altoCm === null) {
            $biblioteca->setAnchoTarjetaPt(null);
            $biblioteca->setAltoTarjetaPt(null);
            $this->entityManager->flush();

            $io->success(sprintf('"%s" vuelve a usar el tamaño estándar de 3x5" (7.62 x 12.7 cm).', $biblioteca->getNombre()));

            return Command::SUCCESS;
        }

        $biblioteca->setAnchoTarjetaPt((int) round(((float) $anchoCm) * self::PT_POR_CM));
        $biblioteca->setAltoTarjetaPt((int) round(((float) $altoCm) * self::PT_POR_CM));
        $this->entityManager->flush();

        $io->success(sprintf(
            '"%s" ahora genera tarjetas de %s x %s cm.',
            $biblioteca->getNombre(),
            $anchoCm,
            $altoCm
        ));

        return Command::SUCCESS;
    }
}
