<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\NotifierChannel;
use App\Factory\Notifier\ChannelFactory;
use App\Form\NotifierDeleteChannelType;
use App\Repository\UserRepository;
use App\Service\Notifier\ChannelManager;
use App\Service\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class NotifierController extends AbstractController
{
    #[Route('/notifier/add-channel/{type}', name: 'app_notifier_add_channel')]
    #[IsGranted('ROLE_USER')]
    public function addChannel(Request $request, UserManager $userManager, ChannelManager $channelsManager, TranslatorInterface $translator, ChannelFactory $channelFactory, ?int $type = null): Response
    {
        if ($type !== null) {
            $form = $this->createForm(NotifierChannel::CHANNELS[$type]['form']);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if (is_array($form->getData())) {
                    $dto = $channelFactory->createDtoFromFormData($form->getData());
                    $channelsManager->add($type, $userManager->getCurrentUser(), $dto->name, $dto->options);
                    $this->addFlash('success', $translator->trans('flash_notifier_channel_added'));
                    return $this->redirectToRoute('app_notifier_channels');
                }
            }
        } else {
            $form = null;
        }

        return $this->render('dashboard/notifier/add.html.twig', [
            'form' => $form?->createView(),
            'channels_types' => NotifierChannel::CHANNELS,
            'channel_type' => $type,
        ]);
    }

    #[Route('/notifier/edit-channel/{id}', name: 'app_notifier_edit_channel')]
    #[IsGranted('ROLE_USER')]
    public function editChannel(Request $request, UserManager $userManager, ChannelManager $channelsManager, NotifierChannel $channel, ChannelFactory $channelFactory): Response
    {
        if ($channel->getOwner() !== $userManager->getCurrentUser()) {
            throw $this->createNotFoundException(sprintf('Channel not found, id: %s', $channel->getId()));
        }

        $form = $this->createForm(NotifierChannel::CHANNELS[$channel->getType()]['form']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (is_array($form->getData())) {
                $dto = $channelFactory->createDtoFromFormData($form->getData());
                $channelsManager->update($channel, $dto->name, $dto->options);
            }
        }

        if (!$form->isSubmitted()) {
            if ($channel->getOptions() === null) {
                $form->setData(['name' => $channel->getName()]);
            } else {
                $form->setData(array_merge($channel->getOptions(), ['name' => $channel->getName()]));
            }
        }

        return $this->render('dashboard/notifier/edit.html.twig', [
            'form' => $form->createView(),
            'channel' => $channel,
        ]);
    }

    #[Route('/notifier/delete-channel/{id}', name: 'app_notifier_delete_channel')]
    #[IsGranted('ROLE_USER')]
    public function deleteChannel(Request $request, UserManager $userManager, ChannelManager $channelsManager, NotifierChannel $channel, UserRepository $userRepository): Response
    {
        if ($channel->getOwner() !== $userManager->getCurrentUser()) {
            throw $this->createNotFoundException(sprintf('Channel not found, id: %s', $channel->getId()));
        }

        $form = $this->createForm(NotifierDeleteChannelType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $userManager->getCurrentUser();
            $user->removeNotifierChannel($channel);
            $userRepository->save($user, true);
            return $this->redirectToRoute('app_notifier_channels');
        }

        return $this->render('dashboard/notifier/delete.html.twig', [
            'form' => $form->createView(),
            'channel' => $channel,
        ]);
    }
}