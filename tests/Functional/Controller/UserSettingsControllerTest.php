<?php
declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use \Symfony\Bundle\FrameworkBundle\KernelBrowser;

class UserSettingsControllerTest extends WebTestCase
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

    public function testAccessSettingsUnauthorized(): void
    {
        $this->client->request('GET', '/user-settings');
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testUpdateUserSettings(): void
    {
        $testUser = $this->userRepository->findOneByUsername('test1@test.com');

        $this->client->loginUser($testUser);
        $crawler = $this->client->request('GET', '/user-settings');
        $form = $crawler->filter('#user_settings_save')->form();

        $this->assertTrue($form->get('user_settings[language]')->getValue() == 'en');

        $form->setValues(['user_settings[language]' => 'pl']);
        $this->client->submit($form);

        $crawler = $this->client->followRedirect();
        $form = $crawler->filter('#user_settings_save')->form();

        $this->assertTrue($form->get('user_settings[language]')->getValue() == 'pl');
    }

    public function testDeleteUser(): void
    {
        $testUser = $this->userRepository->findOneByUsername('test1@test.com');

        $this->client->loginUser($testUser);

        $crawler = $this->client->request('GET', '/user-settings');

        $form = $crawler->filter('#user_settings_delete_user_delete')->form();
        $form->setValues([
            'user_settings_delete_user[agreeDelete]' => 1,
            'user_settings_delete_user[plainPassword]' => 'Test123#'
        ]);

        $this->client->submit($form);

        $this->assertResponseRedirects('/logout');
    }

    public function testDeleteUserInvalidPassword(): void
    {
        $testUser = $this->userRepository->findOneByUsername('test1@test.com');

        $this->client->loginUser($testUser);

        $crawler = $this->client->request('GET', '/user-settings');

        $form = $crawler->filter('#user_settings_delete_user_delete')->form();
        $form->setValues([
            'user_settings_delete_user[agreeDelete]' => 1,
            'user_settings_delete_user[plainPassword]' => 'Test123'
        ]);

        $this->client->submit($form);

        //this means something went wrong and user was not redirected to logout page
        $this->assertResponseIsSuccessful();
    }

    public function testChangePassword(): void
    {
        $testUser = $this->userRepository->findOneByUsername('test1@test.com');

        $this->client->loginUser($testUser);

        $crawler = $this->client->request('GET', '/user-settings');

        $form = $crawler->filter('#user_settings_password_change_change')->form();
        $form->setValues([
            'user_settings_password_change[currentPassword]' => 'Test123#',
            'user_settings_password_change[newPassword][first]' => 'Test123##',
            'user_settings_password_change[newPassword][second]' => 'Test123##',
        ]);

        $this->client->submit($form);

        $updatedUser = $this->userRepository->findOneByUsername('test1@test.com');
        $this->assertNotEquals($testUser->getPassword(),$updatedUser->getPassword());
    }
}
