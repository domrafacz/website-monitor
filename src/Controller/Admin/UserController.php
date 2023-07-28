<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\Admin\UserEditType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin', name: 'app_admin_')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly UserRepository      $userRepository,
        private readonly TranslatorInterface $translator
    )
    {
    }

    #[Route('/users', name: 'users')]
    #[IsGranted('ROLE_ADMIN')]
    public function dashboard(): Response
    {
        return $this->render('admin/users.html.twig', [
            'users' => $this->userRepository->findAll(),
        ]);
    }

    #[Route('/users/edit/{id}', name: 'users_edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userRepository->save($user, true);
            $this->addFlash('success', $this->translator->trans('flash_save_success'));
            return $this->redirectToRoute('app_admin_users_edit', ['id' => $user->getId()]);
        }

        return $this->render('admin/user/edit.html.twig', [
            'edit_form' => $form
        ]);
    }
}