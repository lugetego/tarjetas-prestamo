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

#[AsCommand(name: 'app:configurar-prefijo-biblioteca', description: 'Define el prefijo de adquisición que una biblioteca usa por default (ej. "GM(2)"), o lo restablece a "GM(1)" si se omite')]
class ConfigurarPrefijoBibliotecaCommand extends Command
{
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
            ->addArgument('prefijo', InputArgument::OPTIONAL, 'Prefijo de adquisición, ej. "GM(2)"');
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

        $prefijo = trim((string) $input->getArgument('prefijo'));

        if ($prefijo === '') {
            $biblioteca->setPrefijoAdquisicionPredeterminado(null);
            $this->entityManager->flush();

            $io->success(sprintf('"%s" vuelve a usar el prefijo estándar "GM(1)".', $biblioteca->getNombre()));

            return Command::SUCCESS;
        }

        $biblioteca->setPrefijoAdquisicionPredeterminado($prefijo);
        $this->entityManager->flush();

        $io->success(sprintf('"%s" ahora usa "%s" como prefijo por default en remisiones nuevas.', $biblioteca->getNombre(), $prefijo));

        return Command::SUCCESS;
    }
}
