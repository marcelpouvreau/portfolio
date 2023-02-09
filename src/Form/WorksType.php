<?php

namespace App\Form;

use App\DTO\WorksCreateFromInput;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorksType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('worksName', options: ['label' => 'Work Name: '])
            ->add('url', UrlType::class, options: ['label' => 'Web site link: '])
            ->add('save', SubmitType::class, options: ['label' => $options['is_edit'] ? 'Edit' : 'Add'])
            ->setMethod($options['is_edit'] ? 'PATCH' : 'POST')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => WorksCreateFromInput::class,
            'is_edit' => false,
        ]);

        $resolver->setAllowedTypes('is_edit', 'bool');
    }
}
