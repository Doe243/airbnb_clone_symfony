<?php

namespace App\Form;

use App\Entity\Ad;
use App\Form\ImageType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class AdType extends AbstractType
{

    /**
     * Permet d'avoir la conf de base d'un champ
     *
     * @param string $label
     * @param string $placeholder
     * @return array
     */

    private function getConfiguration($label, $placeholder) {
        return [
            'label' => $label,
            "attr" => [
                "placeholder" => $placeholder
            ]
            ];

    }


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('title', 
        TextType::class, 
        $this->getConfiguration("Titre", "Taper le titre de votre annonce"))
        ->add('slug',  
        TextType::class, 
        $this->getConfiguration("Adresse web", "Adresse web automatique (optionnel)", [
            "required" => false
        ]))
        ->add('coverImage', 
        UrlType::class, 
        $this->getConfiguration("Url de l'image principale", "Donnez l'adresse d'une image"))
        ->add('introduction', 
        TextType::class, 
        $this->getConfiguration("Introduction", "Donnez une description globale de l'annonce"))
        ->add('content', 
        TextareaType::class, 
        $this->getConfiguration("Description détaillée", "Tapez une description de votre annonce"))
        ->add('rooms', 
        IntegerType::class, 
        $this->getConfiguration("Nombre de chambres", "Le nombre de chambres disponoibles"))
        ->add('price', 
        MoneyType::class, 
        $this->getConfiguration("Prix par nuit", "Indiquez le prix que voulez par nuit"))
        ->add('images', 
        CollectionType::class, [

            'entry_type' => ImageType::class, 
            'allow_add' => true,
            'allow_delete' => true

            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ad::class,
        ]);
    }
}
