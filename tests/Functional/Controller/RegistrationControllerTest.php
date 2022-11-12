<?php
declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    public function testLocalizedRedirect(): void
    {
        $client = static::createClient();
        $client->request('GET', '/register');
        $crawler = $client->followRedirect();

        $input = $crawler->filter('#registration_form_email');
        $this->assertTrue($input->count() == 1);
    }

    public function testRedirectWhenAuthenticated(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $userRepository = $container->get(UserRepository::class);
        $testUser = $userRepository->findOneByUsername('test1@test.com');

        $client->loginUser($testUser);

        $client->request('GET', '/en/register');
        $this->assertResponseRedirects('/dashboard');
    }

    public function testRegisterNewUser(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $crawler = $client->request('GET', '/en/register');

        $form = $crawler->filter('#register_submit')->form();

        $client->submit($form, [
            'registration_form[email]'    => 'test2@test.com',
            'registration_form[plainPassword]' => 'Test123#',
            'registration_form[agreeTerms]' => 1
        ]);

        //check if user is authenticated after registration
        $client->followRedirect();
        $this->assertResponseIsSuccessful();

        /** @var User $user */
        $user = $container
            ->get('doctrine')
            ->getManager()
            ->getRepository(User::class)
            ->findOneByUsername('test2@test.com');

        $this->assertSame('test2@test.com', $user->getUserIdentifier());
    }
}
