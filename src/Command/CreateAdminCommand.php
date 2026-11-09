<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:create-admin', description: 'Crea un usuario administrador, sin biblioteca asociada, que ve todas las remisiones')]
class CreateAdminCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::REQUIRED, 'Nombre de usuario')
            ->addArgument('password', InputArgument::REQUIRED, 'Contraseña en texto plano');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $username = $input->getArgument('username');
        $plainPassword = $input->getArgument('password');

        if ($this->userRepository->findOneBy(['username' => $username])) {
            $io->error(sprintf('Ya existe un usuario con el nombre de usuario "%s".', $username));

            return Command::FAILURE;
        }

        $user = new User();
        $user->setUsername($username);
        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        $user->setRoles(['ROLE_ADMIN']);
        $user->setBiblioteca(null);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf('Usuario administrador "%s" creado correctamente (sin biblioteca).', $username));

        return Command::SUCCESS;
    }
}
