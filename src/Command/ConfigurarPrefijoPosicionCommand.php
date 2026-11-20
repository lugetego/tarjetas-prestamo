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

#[AsCommand(name: 'app:configurar-prefijo-posicion', description: 'Recorre en pt el prefijo+adquisición (renglón superior derecho) hacia la derecha para una biblioteca, o lo restablece si se omite')]
class ConfigurarPrefijoPosicionCommand extends Command
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
            ->addArgument('desplazamientoPt', InputArgument::OPTIONAL, 'Cuántos pt recorrerlo a la derecha (negativo para recorrerlo a la izquierda)');
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

        $desplazamientoPt = $input->getArgument('desplazamientoPt');

        if ($desplazamientoPt === null) {
            $biblioteca->setPrefijoDesplazamientoXPt(null);
            $this->entityManager->flush();

            $io->success(sprintf('"%s" vuelve a usar la posición por default para el prefijo+adquisición.', $biblioteca->getNombre()));

            return Command::SUCCESS;
        }

        $biblioteca->setPrefijoDesplazamientoXPt((float) $desplazamientoPt);
        $this->entityManager->flush();

        $io->success(sprintf('"%s": prefijo+adquisición recorrido %spt.', $biblioteca->getNombre(), $desplazamientoPt));

        return Command::SUCCESS;
    }
}
