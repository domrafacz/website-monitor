<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Validator\PasswordStrength;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:user:create',
    description: 'Create new user',
)]
class CreateUserCommand extends Command
{

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly ValidatorInterface $validator
    )
    {

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'User email')
            ->addArgument('password', InputArgument::REQUIRED, 'User password')
            ->addArgument('admin', InputArgument::OPTIONAL, 'Admin role');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $emailArg = $input->getArgument('email');
        $passwordArg = $input->getArgument('password');
        assert(is_string($emailArg));
        assert(is_string($passwordArg));
        $email = $emailArg;
        $password = $passwordArg;

        if ($this->isPasswordValid($password) === false) {
            $io->error('Given password is not strong enough!');
            return Command::FAILURE;
        }

        if ($this->userRepository->findOneByUsername($email)) {
            $io->error('Given email is already taken!');
            return Command::FAILURE;
        }

        $user = new User();
        $user->setRoles(['ROLE_USER']);
        $user->setEmail($email);
        $user->setPassword(
            $this->userPasswordHasher->hashPassword(
                $user,
                $password
            )
        );
        $user->setLanguage('en');

        if ($input->hasArgument('admin') && $input->getArgument('admin') === 'admin') {
            $user->setRoles(array_merge($user->getRoles(), ['ROLE_ADMIN']));
        }

        $this->userRepository->save($user, true);

        $io->success('User has been added!');

        return Command::SUCCESS;
    }

    private function isPasswordValid(string $password): bool
    {
        $passwordStrength = new PasswordStrength();
        $errors = $this->validator->validate(
            $password,
            $passwordStrength
        );

        if ($errors->count() > 0) {
            return false;
        }

        return true;
    }
}
