<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use App\Repository\WebsiteRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin', name: 'app_admin_')]
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    #[IsGranted('ROLE_ADMIN')]
    public function dashboard(WebsiteRepository $websiteRepository, UserRepository $userRepository): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'website_count' => $websiteRepository->count([]),
            'website_count_active' => $websiteRepository->count(['enabled' => true]),
            'user_count' => $userRepository->count([]),
        ]);
    }
}
