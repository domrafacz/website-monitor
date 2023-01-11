<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security\Voter;

use App\Entity\User;
use App\Entity\Website;
use App\Security\Voter\WebsiteVoter;
use App\Tests\Unit\Traits\UserTrait;
use App\Tests\Unit\Traits\WebsiteTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class WebsiteVoterTest extends TestCase
{
    use UserTrait;
    use WebsiteTrait;

    private TokenInterface $token;
    private Website $website;
    private ?User $user;

    protected function setUp(): void
    {
        $this->user = $this->createUser(10);
        $this->website = $this->createWebsite(10);
        $this->token = $this->createMock(TokenInterface::class);
        $this->token->method('getUser')->will($this->returnCallback(
            function () {
                return $this->user;
            }
        ));
    }

    public function testOwnerCanView(): void
    {
        $this->website->setOwner($this->user);
        $voter = new WebsiteVoter();
        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $voter->vote($this->token, $this->website, ['view']));
    }

    public function testOwnerCanEdit(): void
    {
        $this->website->setOwner($this->user);
        $voter = new WebsiteVoter();
        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $voter->vote($this->token, $this->website, ['edit']));
    }

    public function testNotOwnerCannotView(): void
    {
        $voter = new WebsiteVoter();
        $this->assertEquals(VoterInterface::ACCESS_DENIED, $voter->vote($this->token, $this->website, ['view']));
    }

    public function testNotOwnerCannotEdit(): void
    {
        $voter = new WebsiteVoter();
        $this->assertEquals(VoterInterface::ACCESS_DENIED, $voter->vote($this->token, $this->website, ['edit']));
    }

    public function testNotSupportedAttribute(): void
    {
        $voter = new WebsiteVoter();
        $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $voter->vote($this->token, $this->website, ['test']));
    }

    public function testInvalidSubject(): void
    {
        $voter = new WebsiteVoter();
        $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $voter->vote($this->token, new \StdClass(), ['view']));
    }

    public function testInvalidUser(): void
    {
        $this->user = null;
        $voter = new WebsiteVoter();
        $this->assertEquals(VoterInterface::ACCESS_DENIED, $voter->vote($this->token, $this->website, ['view']));
    }
}
