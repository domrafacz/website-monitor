<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route('/login')]
    public function redirectLocale(Request $request): Response
    {
        return $this->redirectToRoute('app_login', ['_locale' => $request->getLocale()]);
    }

    #[Route('/{_locale}/login', name: 'app_login', requirements: ['_locale' => '%app.locales%'])]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        //redirect already authenticated users
        if ($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('app_dashboard');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('login/login.html.twig', [
            'controller_name' => 'LoginController',
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }
}
