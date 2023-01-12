<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\Website;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class WebsiteVoter extends Voter
{
    public const VIEW = 'view';
    public const EDIT = 'edit';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT])) {
            return false;
        }

        if (!$subject instanceof Website) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Website $website */
        $website = $subject;

        return match ($attribute) {
            self::VIEW => $this->canView($website, $user),
            self::EDIT => $this->canEdit($website, $user),
            default => throw new \LogicException('This code should not be reached!')
        };
    }

    private function canView(Website $website, User $user): bool
    {
        if ($this->canEdit($website, $user)) {
            return true;
        }

        return false;
    }

    private function canEdit(Website $website, User $user): bool
    {
        return $website->getOwner() === $user;
    }
}
