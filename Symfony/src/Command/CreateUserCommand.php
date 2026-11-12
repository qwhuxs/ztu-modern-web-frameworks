<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:create-user', description: 'Створює користувача або оновлює пароль і роль наявного')]
class CreateUserCommand extends Command
{
    /**
     * Отримує менеджер сутностей для роботи з базою.
     *
     * @param EntityManagerInterface $em Менеджер сутностей Doctrine
     * @param UserPasswordHasherInterface $hasher Сервіс хешування паролів Symfony Security
     */
    public function __construct(private EntityManagerInterface $em, private UserPasswordHasherInterface $hasher)
    {
        parent::__construct();
    }

    /**
     * Описує аргументи команди.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Пошта')
            ->addArgument('password', InputArgument::REQUIRED, 'Пароль')
            ->addArgument('role', InputArgument::OPTIONAL, 'Роль: client, manager або admin', User::ROLE_CLIENT)
            ->addOption('name', null, InputOption::VALUE_REQUIRED, "Ім'я", 'Користувач');
    }

    /**
     * Створює або оновлює користувача з указаною роллю.
     *
     * @param InputInterface $input Аргументи команди
     * @param OutputInterface $output Вивід у термінал
     * @return int Код завершення команди
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $role = (string) $input->getArgument('role');

        if (!in_array($role, User::ROLES, true)) {
            $output->writeln('<error>Роль має бути: client, manager або admin.</error>');

            return Command::FAILURE;
        }

        $email = mb_strtolower(trim((string) $input->getArgument('email')));
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]) ?? new User();
        $user->setEmail($email);
        $user->setName((string) $input->getOption('name'));
        $user->setPassword($this->hasher->hashPassword($user, (string) $input->getArgument('password')));
        $user->setRole($role);

        $this->em->persist($user);
        $this->em->flush();

        $output->writeln("<info>Готово: {$email} має роль {$role}.</info>");

        return Command::SUCCESS;
    }
}