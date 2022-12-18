<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\UserSettingsChangePasswordDto;
use App\Dto\UserSettingsDeleteUserDto;
use App\Form\UserSettingsDeleteUserType;
use App\Form\UserSettingsPasswordChangeType;
use App\Form\UserSettingsType;
use App\Service\UserManager;
use App\Service\UserSettingsManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserSettingsController extends AbstractController
{
    #[Route('/user-settings', name: 'app_user_settings')]
    public function settingsController(Request $request, UserSettingsManager $userSettingsManager, UserManager $userManager, TranslatorInterface $translator, TokenStorageInterface $tokenStorage): Response
    {
        $user = $userManager->getCurrentUser();

        $this->denyAccessUnlessGranted('ROLE_USER');

        $userSettingsDto = $userSettingsManager->get($user);
        $formGeneralSettings = $this->createForm(UserSettingsType::class, $userSettingsDto);

        $userSettingsDeleteUserDto = new UserSettingsDeleteUserDto();
        $formDeleteUser = $this->createForm(UserSettingsDeleteUserType::class, $userSettingsDeleteUserDto);

        $userSettingsChangePasswordDto = new UserSettingsChangePasswordDto();
        $formChangePassword = $this->createForm(UserSettingsPasswordChangeType::class, $userSettingsChangePasswordDto);

        $formGeneralSettings->handleRequest($request);
        $formDeleteUser->handleRequest($request);
        $formChangePassword->handleRequest($request);

        if ($formGeneralSettings->isSubmitted() && $formGeneralSettings->isValid()) {
            $userSettingsManager->update($user, $userSettingsDto);
            return $this->redirectToRoute('app_user_settings');
        }

        if ($formDeleteUser->isSubmitted() && $formDeleteUser->isValid()) {
            $request->getSession()->invalidate();
            $tokenStorage->setToken(null);
            $userManager->delete($user);
            return $this->redirectToRoute('app_logout');
        }

        if ($formChangePassword->isSubmitted() && $formChangePassword->isValid()) {
            $userManager->changePassword($user, $userSettingsChangePasswordDto->newPassword);
            $this->addFlash('success', $translator->trans('password_change_success'));
            return $this->redirectToRoute('app_user_settings');
        }

        return $this->render('user/settings.html.twig', [
            'form_general_settings' => $formGeneralSettings->createView(),
            'form_delete_user' => $formDeleteUser->createView(),
            'form_change_password' => $formChangePassword->createView(),
        ]);
    }
}
