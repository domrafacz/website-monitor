<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;

class RegistrationController extends AbstractController
{
    #[Route('/register')]
    public function redirectLocale(Request $request): Response
    {
        return $this->redirectToRoute('app_register', ['_locale' => $request->getLocale()]);
    }

    #[Route('/{_locale}/register', name: 'app_register', requirements: ['_locale' => '%app.locales%'])]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserRepository $userRepository, UserAuthenticatorInterface $authenticatorManager, FormLoginAuthenticator $formLoginAuthenticator): Response
    {
        //redirect already authenticated users
        if ($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('app_dashboard');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    strval($form->get('plainPassword')->getData())
                )
            );

            $user->setLanguage($request->getLocale());

            $userRepository->save($user, true);

            $rememberMe = new RememberMeBadge();
            $rememberMe->enable();
            $authenticatorManager->authenticateUser($user, $formLoginAuthenticator, $request, [$rememberMe]);

            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
