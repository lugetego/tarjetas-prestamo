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

#[AsCommand(name: 'app:configurar-posicion-tarjeta', description: 'Ajusta en puntos (pt) dónde empiezan el autor y el título dentro de la tarjeta de una biblioteca, o lo restablece a la posición proporcional por default si se omiten ambos')]
class ConfigurarPosicionTarjetaCommand extends Command
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
            ->addArgument('autorMarginTopPt', InputArgument::OPTIONAL, 'Distancia en pt desde el renglón superior hasta el autor')
            ->addArgument('tituloMarginTopPt', InputArgument::OPTIONAL, 'Distancia en pt desde el autor hasta el título');
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

        $autorMarginTopPt = $input->getArgument('autorMarginTopPt');
        $tituloMarginTopPt = $input->getArgument('tituloMarginTopPt');

        if ($autorMarginTopPt === null || $tituloMarginTopPt === null) {
            $biblioteca->setAutorMarginTopPt(null);
            $biblioteca->setTituloMarginTopPt(null);
            $this->entityManager->flush();

            $io->success(sprintf('"%s" vuelve a usar la posición proporcional por default.', $biblioteca->getNombre()));

            return Command::SUCCESS;
        }

        $biblioteca->setAutorMarginTopPt((float) $autorMarginTopPt);
        $biblioteca->setTituloMarginTopPt((float) $tituloMarginTopPt);
        $this->entityManager->flush();

        $io->success(sprintf(
            '"%s": autor a %spt del renglón superior, título a %spt del autor.',
            $biblioteca->getNombre(),
            $autorMarginTopPt,
            $tituloMarginTopPt
        ));

        return Command::SUCCESS;
    }
}
