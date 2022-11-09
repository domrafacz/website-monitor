<?php
declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    public function testRegisterNewUser(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $crawler = $client->request('GET', '/register');

        $buttonCrawlerNode = $crawler->selectButton('Register');
        $form = $buttonCrawlerNode->form();

        $client->submit($form, [
            'registration_form[email]'    => 'test@test.com',
            'registration_form[plainPassword]' => 'Test123#',
            'registration_form[agreeTerms]' => 1
        ]);

        //check if user is authenticated after registration
        $crawler = $client->followRedirect();
        $this->assertResponseIsSuccessful();

        /** @var User $user */
        $user = $container
            ->get('doctrine')
            ->getManager()
            ->getRepository(User::class)
            ->findOneByUsername('test@test.com');

        $this->assertSame('test@test.com', $user->getUserIdentifier());
    }
}