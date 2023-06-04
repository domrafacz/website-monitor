<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\NotifierChannel;
use App\Service\UserManager;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
        $criteria = Criteria::create()
            ->orderBy(array('id' => Criteria::ASC));

        $websites = $userManager->getCurrentUser()->getWebsites()->matching($criteria);

        return $this->render('dashboard/websites.html.twig', [
            'websites' => $websites,
        ]);
    }

    #[Route('/notifier-channels', name: 'app_notifier_channels')]
    #[IsGranted('ROLE_USER')]
    public function notifierChannels(UserManager $userManager): Response
    {
        $criteria = Criteria::create()
            ->orderBy(array('id' => Criteria::ASC));

        $notifierChannels = $userManager->getCurrentUser()->getNotifierChannels()->matching($criteria);

        return $this->render('dashboard/notifier_channels.html.twig', [
            'notifier_channels' => $notifierChannels,
            'channels_types' => NotifierChannel::CHANNELS,
        ]);
    }
}
