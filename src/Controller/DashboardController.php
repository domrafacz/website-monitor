<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\WebsiteRepository;
use App\Service\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    #[IsGranted('ROLE_USER')]
    public function dashboard(): Response
    {
        return $this->render('dashboard/dashboard.html.twig');
    }

    #[Route('/websites', name: 'app_websites')]
    #[IsGranted('ROLE_USER')]
    public function websites(UserManager $userManager): Response
    {
        $websites = $userManager->getCurrentUser()->getWebsites();

        return $this->render('dashboard/websites.html.twig', [
            'websites' => $websites,
        ]);
    }
}
