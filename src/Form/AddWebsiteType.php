<?php
declare(strict_types=1);

namespace App\Form;

use App\Dto\WebsiteDto;
use App\Entity\Website;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddWebsiteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('url', TextType::class, [
                'label' => 'url',
            ])
            ->add('requestMethod', ChoiceType::class, [
                'choices'  => [
                    'GET' => 'GET',
                    'POST' => 'POST',
                    'PUT' => 'PUT',
                ],
                'label' => 'request_method',
                'choice_translation_domain' => false,
            ])
            ->add('maxRedirects', IntegerType::class, [
                'label' => 'max_redirects'
            ])
            ->add('timeout', IntegerType::class, [
                'label' => 'timeout_seconds'
            ])
            ->add('frequency', IntegerType::class, [
                'label' => 'frequency_minutes'
            ])
            ->add('expectedStatusCode', IntegerType::class, [
                'label' => 'expected_status_code'
            ])
            ->add('enabled', ChoiceType::class, [
                'choices' => [
                    'yes' => true,
                    'no' => false,
                ],
                'label' => 'enabled',
            ])
            ->add('add', SubmitType::class, [
                'label' => 'save',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => WebsiteDto::class,
        ]);
    }
}
