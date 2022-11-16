<?php
declare(strict_types=1);

namespace App\Form;

use App\Dto\UserSettingsChangePasswordDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserSettingsPasswordChangeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentPassword', PasswordType::class, [
                'mapped' => true,
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'enter_password',
                    ]),
                ],
                'label' => 'current_password',
            ])
            ->add(
                'newPassword',
                RepeatedType::class,
                [
                    'type' => PasswordType::class,
                    'invalid_message' => 'new_passwords_not_identical',
                    'required' => true,
                    'first_options' => ['label' => 'new_password'],
                    'second_options' => ['label' => 'repeat_password'],
                ]
            )
            ->add('change', SubmitType::class, [
                'label' => 'change_password',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserSettingsChangePasswordDto::class,
        ]);
    }
}
