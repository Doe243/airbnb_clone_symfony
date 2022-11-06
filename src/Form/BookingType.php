<?php

namespace App\Form;

use App\Entity\Booking;
use App\Form\ApplicationType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Form\DataTransformer\FrenchToDateTimeTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class BookingType extends ApplicationType
{

    public function __construct(FrenchToDateTimeTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startDate', 
                    TextType::class, 
                    $this->getConfiguration("Date d'arrivée", "Veuillez choisir votre date d'arriver"))
            ->add('endDate', 
            TextType::class, 
                    $this->getConfiguration("Date de départ", "Veuiller choisir votre date de départ"))
            ->add('comment', 
                    TextareaType::class, 
                    $this->getConfiguration(false, "Si vous avez un commentaire, n'hésiter pas à nous en faire part !", ["required" => false]))
        ;

        $builder->get('startDate')->addModelTransformer($this->transformer);
        $builder->get('endDate')->addModelTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Booking::class,
        ]);
    }
}
