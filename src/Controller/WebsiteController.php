<?php
declare(strict_types=1);

namespace App\Controller;

use App\Dto\WebsiteDto;
use App\Factory\WebsiteFactory;
use App\Form\AddWebsiteType;
use App\Service\UserManager;
use App\Service\WebsiteManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WebsiteController extends AbstractController
{
    #[Route('/websites/add', name: 'app_websites_add')]
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
}