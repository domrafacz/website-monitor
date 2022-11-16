<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserManager
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function delete(User $user): void
    {
        $this->userRepository->remove($user, true);
    }

    public function changePassword(User $user, string $plainPassword): void
    {
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $plainPassword
        );

        $this->userRepository->upgradePassword($user, $hashedPassword);
    }
}