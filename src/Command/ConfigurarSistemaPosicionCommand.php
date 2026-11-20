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

#[AsCommand(name: 'app:configurar-sistema-posicion', description: 'Ajusta en pt la posición vertical del "No. de sistema" dentro de la tarjeta de una biblioteca, o lo restablece a la posición proporcional por default si se omite')]
class ConfigurarSistemaPosicionCommand extends Command
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
            ->addArgument('sistemaTopPt', InputArgument::OPTIONAL, 'Distancia en pt desde arriba de la tarjeta hasta el número de sistema');
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

        $sistemaTopPt = $input->getArgument('sistemaTopPt');

        if ($sistemaTopPt === null) {
            $biblioteca->setSistemaTopPt(null);
            $this->entityManager->flush();

            $io->success(sprintf('"%s" vuelve a usar la posición proporcional por default para el número de sistema.', $biblioteca->getNombre()));

            return Command::SUCCESS;
        }

        $biblioteca->setSistemaTopPt((float) $sistemaTopPt);
        $this->entityManager->flush();

        $io->success(sprintf('"%s": número de sistema a %spt desde arriba de la tarjeta.', $biblioteca->getNombre(), $sistemaTopPt));

        return Command::SUCCESS;
    }
}
