<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\NotifierChannel;
use App\Factory\Notifier\ChannelFactory;
use App\Form\NotifierDeleteChannelType;
use App\Form\NotifierTestChannelType;
use App\Repository\UserRepository;
use App\Service\Notifier\ChannelManager;
use App\Service\Notifier\Notifier;
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
    public function editChannel(Request $request, UserManager $userManager, ChannelManager $channelManager, ChannelFactory $channelFactory, int $id): Response
    {
        $channel = $userManager->getNotifierChannel($userManager->getCurrentUser(), $id);

        $form = $this->createForm(NotifierChannel::CHANNELS[$channel->getType()]['form']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (is_array($form->getData())) {
                $dto = $channelFactory->createDtoFromFormData($form->getData());
                $channelManager->update($channel, $dto->name, $dto->options);
            }
        }

        if (!$form->isSubmitted()) {
            $options = ['name' => $channel->getName()];
            if ($channel->getOptions() !== null) {
                $options = array_merge($channel->getOptions(), $options);
            }
            $form->setData($options);
        }

        return $this->render('dashboard/notifier/edit.html.twig', [
            'form' => $form->createView(),
            'channel' => $channel,
        ]);
    }

    #[Route('/notifier/delete-channel/{id}', name: 'app_notifier_delete_channel')]
    #[IsGranted('ROLE_USER')]
    public function deleteChannel(Request $request, UserManager $userManager, ChannelManager $channelsManager, UserRepository $userRepository, int $id): Response
    {
        $channel = $userManager->getNotifierChannel($userManager->getCurrentUser(), $id);

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

    #[Route('/notifier/test-channel/{id}', name: 'app_notifier_test_channel')]
    #[IsGranted('ROLE_USER')]
    public function testChannel(Request $request, UserManager $userManager, Notifier $notifier, TranslatorInterface $translator, int $id): Response
    {
        $channel = $userManager->getNotifierChannel($userManager->getCurrentUser(), $id);

        $form = $this->createForm(NotifierTestChannelType::class);
        $form->handleRequest($request);
        $result = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $result = $notifier->sendNotification($channel->getType(), 'test_subject', 'test_message', $channel->getOptions());
        }

        return $this->render('dashboard/notifier/test.html.twig', [
            'form' => $form->createView(),
            'channel' => $channel,
            'result' => $result,
        ]);
    }
}