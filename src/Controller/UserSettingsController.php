<?php
declare(strict_types=1);

namespace App\Controller;

use App\Dto\UserSettingsDeleteUserDto;
use App\Entity\User;
use App\Form\UserSettingsDeleteUserType;
use App\Form\UserSettingsType;
use App\Service\UserManager;
use App\Service\UserSettingsManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserSettingsController extends AbstractController
{
    #[Route('/user-settings', name: 'app_user_settings')]
    public function settingsController(Request $request, UserSettingsManager $userSettingsManager, UserManager $userManager, UserPasswordHasherInterface $passwordHasher, TranslatorInterface $translator, TokenStorageInterface $tokenStorage): Response
    {
        if (!$user = $this->getUser()) {
            throw new UserNotFoundException();
        }

        /** @var User $user */
        $this->denyAccessUnlessGranted('ROLE_USER');

        $userSettingsDto = $userSettingsManager->get($user);
        $formGeneralSettings = $this->createForm(UserSettingsType::class, $userSettingsDto);

        $userSettingsDeleteUserDto = new UserSettingsDeleteUserDto();
        $formDeleteUser = $this->createForm(UserSettingsDeleteUserType::class, $userSettingsDeleteUserDto);


        $formGeneralSettings->handleRequest($request);
        $formDeleteUser->handleRequest($request);

        if ($formGeneralSettings->isSubmitted() && $formGeneralSettings->isValid()) {
            $userSettingsManager->update($user, $userSettingsDto);
            return $this->redirectToRoute('app_user_settings');
        }

        if ($formDeleteUser->isSubmitted() && $formDeleteUser->isValid()) {
            if (!$passwordHasher->isPasswordValid($user, $userSettingsDeleteUserDto->plainPassword)) {
                $this->addFlash('error', $translator->trans('incorrect_password'));
            } else {
                $request->getSession()->invalidate();
                $tokenStorage->setToken(null);
                $userManager->delete($user);
                return $this->redirectToRoute('app_logout');
            }
        }

        return $this->render('user/settings.html.twig', [
            'form_general_settings' => $formGeneralSettings->createView(),
            'form_delete_user' => $formDeleteUser->createView(),
        ]);
    }
}
