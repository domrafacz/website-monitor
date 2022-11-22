<?php
declare(strict_types=1);

namespace App\Controller;

use App\Dto\WebsiteDto;
use App\Factory\WebsiteFactory;
use App\Form\AddWebsiteType;
use App\Form\DeleteWebsiteType;
use App\Service\UserManager;
use App\Service\WebsiteManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
    public function edit(int $id, Request $request, WebsiteFactory $websiteFactory, WebsiteManager $websiteManager, TranslatorInterface $translator, UserManager $userManager): Response
    {
        if (!$website = $userManager->getCurrentUser()->findWebsite($id))
        {
            throw new NotFoundHttpException(sprintf('Website not found, id: %s', $id));
        }

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
    public function details(int $id, Request $request, UserManager $userManager, WebsiteManager $websiteManager, TranslatorInterface $translator): Response
    {
        if (!$website = $userManager->getCurrentUser()->findWebsite($id))
        {
            throw new NotFoundHttpException(sprintf('Website not found, id: %s', $id));
        }

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
        ]);
    }
}