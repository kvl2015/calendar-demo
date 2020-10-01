<?php

namespace App\Form;

use App\Entity\Booking;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class BookingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, [
                'attr' => ['autofocus' => true],
                'label' => 'label.title',
            ])
            ->add('beginAt', DateType::class, [
                'widget' => 'single_text',
                'label' => 'label.begin_at',
            ])
            ->add('startTime', TimeType::class, [
                'label' => 'label.start_time',
                'widget' => 'single_text',
            ])
            ->add('endTime', TimeType::class, [
                'label' => 'label.end_time',
                'widget' => 'single_text',
            ])
            ->add('status', ChoiceType::class, [
                'choices'  => [
                    'select option' => '',
                    'label.canceled' => 'canceled'
                ],
                'label' => 'label.status',
                'required' => false
            ])
            ->add('color', ColorType::class, [
                'label' => 'label.color',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'label.description',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Booking::class,
        ]);
    }
}
