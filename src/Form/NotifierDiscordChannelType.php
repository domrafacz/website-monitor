<?php
declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class NotifierDiscordChannelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'notifier_channel_name',
            ])
            ->add('webhook', TextType::class, [
                'label' => 'discord_webhook',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'save',
            ])
        ;
    }
}
