<?php
declare(strict_types=1);

namespace App\Tests\Functional\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class UserRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
        unset($this->entityManager);
    }

    public function testUpgradePasswordInvalidUserType(): void
    {
        $userRepository = $this->entityManager->getRepository(User::class);

        $user = new class implements PasswordAuthenticatedUserInterface {
            public function getPassword(): ?string
            {
                return 'hashedpassword';
            }
        };

        $this->expectException(UnsupportedUserException::class);
        $userRepository->upgradePassword($user, 'newpassword');
    }
}