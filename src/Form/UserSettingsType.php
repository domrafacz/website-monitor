<?php
declare(strict_types=1);

namespace App\Form;

use App\Dto\UserSettingsDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('language', ChoiceType::class, [
                'choices' => [
                    'english' => 'en',
                    'polish' => 'pl',
                ],
                'label' => 'language',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'save',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserSettingsDto::class,
        ]);
    }
}
