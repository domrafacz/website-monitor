<?php
declare(strict_types=1);

namespace App\Controller;

use App\Dto\WebsiteDto;
use App\Entity\Website;
use App\Factory\WebsiteFactory;
use App\Form\AddWebsiteType;
use App\Repository\WebsiteRepository;
use App\Service\UserManager;
use App\Service\WebsiteManager;
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
    public function details(Website $website): Response
    {
        //add post delete form
        return $this->render('dashboard/website/details.html.twig', [
           'website' => $website,
        ]);
    }

}