<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\UserRepository;
use App\Validator\PasswordStrength;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:user:change-password',
    description: 'Change password for an existing user',
)]
class ChangePasswordCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly ValidatorInterface $validator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email of the user whose password should be changed');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $input->getArgument('email');
        assert(is_string($email));

        $user = $this->userRepository->findOneByUsername($email);

        if ($user === null) {
            $io->error(sprintf('User with email "%s" not found.', $email));
            return Command::FAILURE;
        }

        $helper = $this->getHelper('question');

        $passwordQuestion = new Question('New password: ');
        $passwordQuestion->setHidden(true);
        $passwordQuestion->setHiddenFallback(false);

        $confirmQuestion = new Question('Confirm new password: ');
        $confirmQuestion->setHidden(true);
        $confirmQuestion->setHiddenFallback(false);

        /** @var string|null $password */
        $password = $helper->ask($input, $output, $passwordQuestion);

        if (empty($password)) {
            $io->error('Password cannot be empty.');
            return Command::FAILURE;
        }

        /** @var string|null $confirm */
        $confirm = $helper->ask($input, $output, $confirmQuestion);

        if ($password !== $confirm) {
            $io->error('Passwords do not match.');
            return Command::FAILURE;
        }

        $errors = $this->validator->validate($password, new PasswordStrength());
        if ($errors->count() > 0) {
            $io->error('Given password is not strong enough!');
            return Command::FAILURE;
        }

        $user->setPassword(
            $this->userPasswordHasher->hashPassword($user, $password)
        );

        $this->userRepository->save($user, true);

        $io->success(sprintf('Password for user "%s" has been changed successfully.', $email));

        return Command::SUCCESS;
    }
}

