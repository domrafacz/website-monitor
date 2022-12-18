<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class NotifierTelegramChannelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'notifier_channel_name',
            ])
            ->add('apiToken', TextType::class, [
                'label' => 'telegram_api_token',
            ])
            ->add('chatId', TextType::class, [
                'label' => 'telegram_chat_id',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'save',
            ])
        ;
    }
}
