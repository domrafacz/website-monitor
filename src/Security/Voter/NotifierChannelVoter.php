<?php

namespace App\Security\Voter;

use App\Entity\NotifierChannel;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class NotifierChannelVoter extends Voter
{
    public const VIEW = 'view';
    public const EDIT = 'edit';

    protected function supports(string $attribute, $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT])) {
            return false;
        }

        if (!$subject instanceof NotifierChannel) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var NotifierChannel $notifierChannel */
        $notifierChannel = $subject;

        return match ($attribute) {
            self::VIEW => $this->canView($notifierChannel, $user),
            self::EDIT => $this->canEdit($notifierChannel, $user),
            default => throw new \LogicException('This code should not be reached!')
        };
    }

    private function canView(NotifierChannel $notifierChannel, User $user): bool
    {
        if ($this->canEdit($notifierChannel, $user)) {
            return true;
        }

        return false;
    }

    private function canEdit(NotifierChannel $notifierChannel, User $user): bool
    {
        return $notifierChannel->getOwner() === $user;
    }
}
