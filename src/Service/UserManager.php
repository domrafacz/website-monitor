<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\NotifierChannel;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UserManager
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly Security $security,
    ) {
    }

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

    public function getCurrentUser(): User
    {
        if (!$user = $this->security->getUser()) {
            throw new UserNotFoundException();
        }

        if (!$user instanceof User) {
            throw new UnexpectedTypeException('Wrong user instance', User::class);
        }

        return $user;
    }

    public function getNotifierChannel(User $user, int $id): NotifierChannel
    {
        $channel = $user->findNotifierChannel($id);

        if (!$channel) {
            throw new NotFoundHttpException(sprintf('Channel not found, id: %s', $id));
        }

        return $channel;
    }
}
