<?php
declare(strict_types=1);

namespace App\Controller;

use App\Factory\UserSettingsFactory;
use App\Form\UserSettingsType;
use App\Service\UserSettingsManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserSettingsController extends AbstractController
{
    #[Route('/user-settings', name: 'app_user_settings')]
    public function settingsController(Request $request, UserSettingsFactory $userSettingsFactory, UserSettingsManager $userSettingsManager): Response
    {
        $userSettingsDto = $userSettingsFactory->createDto($this->getUser());
        $form = $this->createForm(UserSettingsType::class, $userSettingsDto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userSettingsManager->update($this->getUser(), $userSettingsDto);
            return $this->redirectToRoute('app_user_settings');
        }

        return $this->render('user/settings.html.twig', [
            'form' => $form->createView(),
        ]);

    }
}