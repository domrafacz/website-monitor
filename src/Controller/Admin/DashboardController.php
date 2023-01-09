<?php

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use App\Repository\WebsiteRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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
