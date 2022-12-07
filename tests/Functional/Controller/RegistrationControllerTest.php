<?php
declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->userRepository = static::getContainer()->get(UserRepository::class);
    }

    protected function tearDown(): void
    {
        unset($this->client);
        unset($this->userRepository);
        parent::tearDown();
    }

    public function testLocalizedRedirect(): void
    {
        $this->client->request('GET', '/register');
        $crawler = $this->client->followRedirect();

        $input = $crawler->filter('#registration_form_email');
        $this->assertTrue($input->count() == 1);
    }

    public function testRedirectWhenAuthenticated(): void
    {
        $testUser = $this->userRepository->findOneByUsername('test1@test.com');

        $this->client->loginUser($testUser);

        $this->client->request('GET', '/en/register');
        $this->assertResponseRedirects('/dashboard');
    }

    public function testRegisterNewUser(): void
    {
        $crawler = $this->client->request('GET', '/en/register');

        $form = $crawler->filter('#register_submit')->form();

        $this->client->submit($form, [
            'registration_form[email]'    => 'test2@test.com',
            'registration_form[plainPassword]' => 'Test123#',
            'registration_form[agreeTerms]' => 1
        ]);

        //check if user is authenticated after registration
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        /** @var User $user */
        $user = $this->userRepository->findOneByUsername('test2@test.com');

        $this->assertSame('test2@test.com', $user->getUserIdentifier());
    }
}
