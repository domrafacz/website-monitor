<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class NotifierMatrixChannelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'notifier_channel_name',
            ])
            ->add('serverUrl', TextType::class, [
                'label' => 'matrix_server_url',
                'attr' => [
                    'placeholder' => 'https://matrix.org',
                ],
            ])
            ->add('accessToken', TextType::class, [
                'label' => 'matrix_access_token',
            ])
            ->add('roomId', TextType::class, [
                'label' => 'matrix_room_id',
                'attr' => [
                    'placeholder' => '!room_id:matrix.org',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'save',
            ])
        ;
    }
}
