<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security\Voter;

use App\Entity\NotifierChannel;
use App\Entity\User;
use App\Security\Voter\NotifierChannelVoter;
use App\Tests\Unit\Traits\NotifierChannelTrait;
use App\Tests\Unit\Traits\UserTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class NotifierChannelVoterTest extends TestCase
{
    use NotifierChannelTrait;
    use UserTrait;

    private TokenInterface $token;
    private NotifierChannel $notifierChannel;
    private ?User $user;

    protected function setUp(): void
    {
        $this->user = $this->createUser(10);
        $this->notifierChannel = $this->createNotifierChannel(10, $this->user);
        $this->token = $this->createMock(TokenInterface::class);
        $this->token->method('getUser')->will($this->returnCallback(
            function () {
                return $this->user;
            }
        ));
    }

    public function testOwnerCanView(): void
    {
        $this->notifierChannel->setOwner($this->user);
        $voter = new NotifierChannelVoter();
        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $voter->vote($this->token, $this->notifierChannel, ['view']));
    }

    public function testOwnerCanEdit(): void
    {
        $this->notifierChannel->setOwner($this->user);
        $voter = new NotifierChannelVoter();
        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $voter->vote($this->token, $this->notifierChannel, ['edit']));
    }

    public function testNotOwnerCannotView(): void
    {
        $this->notifierChannel->setOwner(null);
        $voter = new NotifierChannelVoter();
        $this->assertEquals(VoterInterface::ACCESS_DENIED, $voter->vote($this->token, $this->notifierChannel, ['view']));
    }

    public function testNotOwnerCannotEdit(): void
    {
        $this->notifierChannel->setOwner(null);
        $voter = new NotifierChannelVoter();
        $this->assertEquals(VoterInterface::ACCESS_DENIED, $voter->vote($this->token, $this->notifierChannel, ['edit']));
    }

    public function testNotSupportedAttribute(): void
    {
        $voter = new NotifierChannelVoter();
        $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $voter->vote($this->token, $this->notifierChannel, ['test']));
    }

    public function testInvalidSubject(): void
    {
        $voter = new NotifierChannelVoter();
        $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $voter->vote($this->token, new \StdClass(), ['view']));
    }

    public function testInvalidUser(): void
    {
        $this->user = null;
        $voter = new NotifierChannelVoter();
        $this->assertEquals(VoterInterface::ACCESS_DENIED, $voter->vote($this->token, $this->notifierChannel, ['view']));
    }
}
