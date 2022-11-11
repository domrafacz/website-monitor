<?php
declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserSettingsControllerTest extends WebTestCase
{
    public function testUpdateUserSettings(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $userRepository = $container->get(UserRepository::class);
        $testUser = $userRepository->findOneByUsername('test1@test.com');

        $client->loginUser($testUser);
        $crawler = $client->request('GET', '/user-settings');
        $form = $crawler->selectButton('Save')->form();

        $this->assertTrue($form->get('user_settings[language]')->getValue() == 'en');

        $form->setValues(['user_settings[language]' => 'pl']);
        $client->submit($form);

        $crawler = $client->followRedirect();
        $form = $crawler->selectButton('Save')->form();

        $this->assertTrue($form->get('user_settings[language]')->getValue() == 'pl');
    }
}