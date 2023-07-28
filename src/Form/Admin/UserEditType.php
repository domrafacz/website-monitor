<?php

namespace App\Form\Admin;

use App\Entity\User;
use App\Enum\UserStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'email',
            ])
            ->add('status', EnumType::class, [
                'class' => UserStatus::class,
                'choice_label' => fn ($choice) => match ($choice) {
                    UserStatus::ACTIVE => 'user_status_active',
                    UserStatus::INACTIVE => 'user_status_inactive',
                    UserStatus::BLOCKED  => 'user_status_blocked',
                },
                'label' => 'status',
            ])
            ->add('quota', IntegerType::class, [
                'label' => 'quota',
            ])
            ->add('add', SubmitType::class, [
                'label' => 'save',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
