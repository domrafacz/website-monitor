<?php
declare(strict_types=1);

namespace App\Controller;

use App\Form\UserSettingsType;
use App\Service\UserSettingsManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class UserSettingsController extends AbstractController
{
    #[Route('/user-settings', name: 'app_user_settings')]
    public function settingsController(Request $request, UserSettingsManager $userSettingsManager): Response
    {
        if (!$user = $this->getUser()) {
            throw new UserNotFoundException();
        }

        $this->denyAccessUnlessGranted('ROLE_USER');

        $userSettingsDto = $userSettingsManager->get($user);
        $form = $this->createForm(UserSettingsType::class, $userSettingsDto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userSettingsManager->update($user, $userSettingsDto);
            return $this->redirectToRoute('app_user_settings');
        }

        return $this->render('user/settings.html.twig', [
            'form' => $form->createView(),
        ]);

    }
}
