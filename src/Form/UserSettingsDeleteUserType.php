<?php

declare(strict_types=1);

namespace App\Form;

use App\Dto\UserSettingsDeleteUserDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserSettingsDeleteUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('agreeDelete', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'agree_user_delete_error',
                    ]),
                ],
                'label' => 'agree_user_delete',
                'label_attr' => [
                    'class' => 'text-danger',
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'enter_password',
                    ]),
                ],
                'label' => 'password',
            ])
            ->add('delete', SubmitType::class, [
                'label' => 'delete_account',
                'attr' => ['class' => 'btn-danger'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserSettingsDeleteUserDto::class,
        ]);
    }
}
