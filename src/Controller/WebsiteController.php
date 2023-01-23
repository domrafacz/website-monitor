<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\WebsiteDto;
use App\Entity\NotifierChannel;
use App\Entity\Website;
use App\Factory\WebsiteFactory;
use App\Form\AddWebsiteType;
use App\Form\DeleteWebsiteType;
use App\Repository\WebsiteRepository;
use App\Service\UserManager;
use App\Service\WebsiteManager;
use App\Service\WebsiteStatisticsProvider;
use Doctrine\Common\Collections\Criteria;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class WebsiteController extends AbstractController
{
    #[Route('/website/add', name: 'app_website_add')]
    #[IsGranted('ROLE_USER')]
    public function add(Request $request, WebsiteFactory $websiteFactory, WebsiteManager $websiteManager, UserManager $userManager): Response
    {
        if ($userManager->exceedsQuota($userManager->getCurrentUser())) {
            return $this->redirectToRoute('app_websites');
        }

        $websiteDto = new WebsiteDto();
        $form = $this->createForm(AddWebsiteType::class, $websiteDto);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $websiteManager->addOwner($websiteFactory->createFromDto($websiteDto), $userManager->getCurrentUser(), true);
            return $this->redirectToRoute('app_websites');
        }

        return $this->render('dashboard/website/add.html.twig', [
            'add_form' => $form->createView(),
        ]);
    }

    #[Route('/website/edit/{id}', name: 'app_website_edit')]
    #[IsGranted('ROLE_USER')]
    #[IsGranted('view', subject: 'website', statusCode: 404)]
    public function edit(Website $website, Request $request, WebsiteFactory $websiteFactory, WebsiteManager $websiteManager, TranslatorInterface $translator): Response
    {
        $websiteDto = $websiteFactory->createDto($website);
        $form = $this->createForm(AddWebsiteType::class, $websiteDto);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $websiteManager->edit($website, $websiteDto);
            $this->addFlash('success', $translator->trans('flash_change_success'));
            return $this->redirectToRoute('app_websites');
        }

        return $this->render('dashboard/website/edit.html.twig', [
            'add_form' => $form->createView(),
        ]);
    }

    #[Route('/website/details/{id}', name: 'app_website_details')]
    #[IsGranted('ROLE_USER')]
    #[IsGranted('view', subject: 'website', statusCode: 404)]
    public function details(Website $website, Request $request, WebsiteManager $websiteManager, TranslatorInterface $translator, WebsiteStatisticsProvider $statisticsProvider): Response
    {
        $deleteForm = $this->createForm(DeleteWebsiteType::class);
        $deleteForm->handleRequest($request);

        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $websiteManager->delete($website, true);
            $this->addFlash('success', $translator->trans('flash_website_deleted'));
            return $this->redirectToRoute('app_websites');
        }

        return $this->render('dashboard/website/details.html.twig', [
            'website' => $website,
            'delete_form' => $deleteForm->createView(),
            'statistics_provider' => $statisticsProvider,
            'average_response_time_24h' => $statisticsProvider->getAverageResponseTime24H($website),
        ]);
    }

    #[Route('/website/incidents/{id}', name: 'app_website_incidents')]
    #[IsGranted('ROLE_USER')]
    #[IsGranted('view', subject: 'website', statusCode: 404)]
    public function incidents(Website $website): Response
    {
        $criteria = Criteria::create()
            ->orderBy(array('id' => Criteria::DESC));

        $downtimeLogs = $website->getDowntimeLogs()->matching($criteria);

        return $this->render('dashboard/website/incidents.html.twig', [
           'downtime_logs' => $downtimeLogs,
        ]);
    }

    #[Route('/website/toggle-notifier-channel/{website_id}/{channel_id}/{token}', name: 'app_website_toggle_notifier_channel')]
    #[Entity('website', options: ['id' => 'website_id'])]
    #[Entity('notifierChannel', options: ['id' => 'channel_id'])]
    #[IsGranted('ROLE_USER')]
    #[IsGranted('view', subject: 'website', statusCode: 404)]
    #[IsGranted('view', subject: 'notifierChannel', statusCode: 404)]
    public function toggleNotifierChannel(Website $website, NotifierChannel $notifierChannel, string $token, WebsiteRepository $websiteRepository): Response
    {
        if ($this->isCsrfTokenValid('website-toggle-notifier-channel', $token)) {
            $website->toggleNotifierChannel($notifierChannel);
            $websiteRepository->save($website, true);
        }

        return $this->redirectToRoute('app_website_details', ['id' => $website->getId()]);
    }
}
