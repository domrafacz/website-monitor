<?php
declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeleteWebsiteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('config', CheckboxType::class, [
                'label' => 'website_delete_checkbox_label',
                'label_attr' => ['class' => 'text-danger'],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'delete',
                'attr' => ['class' => 'btn-danger'],
            ])
        ;
    }
}
