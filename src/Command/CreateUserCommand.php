<?php

namespace App\Command;

use App\Entity\Biblioteca;
use App\Entity\User;
use App\Repository\BibliotecaRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:create-user', description: 'Crea un usuario para acceder al sistema')]
class CreateUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UserRepository $userRepository,
        private readonly BibliotecaRepository $bibliotecaRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::REQUIRED, 'Nombre de usuario')
            ->addArgument('password', InputArgument::REQUIRED, 'Contraseña en texto plano')
            ->addArgument('biblioteca', InputArgument::REQUIRED, 'Nombre de la biblioteca (se crea si no existe)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $username = $input->getArgument('username');
        $plainPassword = $input->getArgument('password');
        $nombreBiblioteca = trim((string) $input->getArgument('biblioteca'));

        if ($this->userRepository->findOneBy(['username' => $username])) {
            $io->error(sprintf('Ya existe un usuario con el nombre de usuario "%s".', $username));

            return Command::FAILURE;
        }

        $biblioteca = $this->bibliotecaRepository->findOneByNombreInsensible($nombreBiblioteca);
        $esNueva = $biblioteca === null;

        if ($biblioteca === null) {
            $biblioteca = new Biblioteca();
            $biblioteca->setNombre($nombreBiblioteca);
            $this->entityManager->persist($biblioteca);
        }

        $user = new User();
        $user->setUsername($username);
        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        $user->setBiblioteca($biblioteca);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf(
            'Usuario "%s" creado correctamente en la biblioteca "%s"%s.',
            $username,
            $biblioteca->getNombre(),
            $esNueva ? ' (biblioteca nueva)' : ''
        ));

        return Command::SUCCESS;
    }
}
