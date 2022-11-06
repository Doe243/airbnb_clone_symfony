<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Entity\Image;
use App\Form\AdType;
use App\Form\ImageType;
use App\Repository\AdRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class AdController extends AbstractController
{
    #[Route('/ads', name: 'ads_index')]

    public function index(AdRepository $repo): Response
    {
        $ads = $repo->findAll();

        return $this->render('ad/index.html.twig', [
            'ads' => $ads,
        ]);
    }

    /**
     * Permet de créer une annonce
     * 
     * @return Response
     */

     #[Route('/ads/new', name:'ads_new')]
     #[IsGranted('ROLE_USER')]

    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $ad = new Ad();

        $user = $this->getUser();

        //1ere façon de créer un formaulaire avec Symfony

        $form = $this->createFormBuilder($ad)

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
                            'allow_add' => true

                    ])
                /* ->add('save', SubmitType::class, [
                    'label' => "Créer la nouvelle annonce",
                    'attr' => [
                        'class' => "btn btn-primary"
                    ]
                 ]) */
                
                ->getForm();
        
         ///2eme façon de créer un formulaire avec symfony 

        //$form = $this->createForm(AdType::class, $ad);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            foreach ($ad->getImages() as $image) {
                
                $image->setAd($ad);
                $em->persist($image);
            }

            $ad->setAuthor($user);
            
            $em->persist($ad);
            $em->flush();

            $this->addFlash(
                'alert-success',
                "L'annonce <strong> {$ad->getTitle()} </strong> a bien été enregistrée !"
            );

            return $this->redirectToRoute("ads_show", [
                'slug' => $ad->getSlug()
            ]);
        }

        return $this->render('ad/new.html.twig', [

            'form' => $form->createView()
        ]);
    }

    /**
     * Permet d'afficher le formulaire d'édition
     * @return Response
     */

     #[Route('/ads/{slug}/edit', name: 'ads_edit')]
     #[Security("is_granted('ROLE_USER') and user === ad.getAuthor()", message: "Cette annonce ne vous appartient pas donc vous ne pouvez pas la modifier")]

    public function edit(Request $request, Ad $ad, EntityManagerInterface $em): Response
    {

        $form = $this->createFormBuilder($ad)

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

                
                ->getForm();


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            foreach ($ad->getImages() as $image) {
                
                $image->setAd($ad);
                $em->persist($image);
            }
            
            $em->persist($ad);
            $em->flush();

            $this->addFlash(
                'alert-success',
                "L'annonce <strong> {$ad->getTitle()} </strong> a bien été modifié !"
            );

            return $this->redirectToRoute("ads_show", [
                'slug' => $ad->getSlug()
            ]);
        }

        return $this->render('ad/edit.html.twig', [

            'form' => $form->createView(),
            'ad' => $ad
        ]);
    }

    /**
     * Permet d'afficher une seule annonce
     *
     * @return Response
     */
    #[Route('/ads/{slug}', name:'ads_show')]

    public function show(Ad $ad): Response
    {
        ///$ad = $repo->findOneBySlug($slug); 

        return $this->render('ad/show.html.twig', [
            "ad" => $ad
        ]);
    }

    /**
     * Permet d'avoir la conf de base d'un champ
     *
     * @param string $label
     * @param string $placeholder
     * @param array $options
     * @return array
     */

    private function getConfiguration($label, $placeholder, $options = []) {
        
        return array_merge(
            [

                'label' => $label,
                "attr" => [
                    "placeholder" => $placeholder
                ]
            ], $options);

    }

    /**
     * Permet de supprimer une annonce
     * 
     * @param Ad $ad
     * @param EntityManagerInterface $em
     * @return Response
     */

    #[Route('/ads/{slug}/delete', name: 'ads_delete')]
    #[Security("is_granted('ROLE_USER') and user == ad.getAuthor()", message: "Vous n'avez pas le droit d'accèder à cette ressource")]

    public function delete(Ad $ad, EntityManagerInterface $em): Response
    {
        $em->remove($ad);
        $em->flush();

        $this->addFlash(

            "alert-success",
            "L'annonce <strong>{$ad->getTitle()}</strong> a bien été supprimer !"
        );

        return $this->redirectToRoute('ads_index');
    }

    /**
     * Permet d'afficher la liste des réservations faites par l'utilisateur
     * 
     * @return Response
     */

     #[Route('/account/bookings', name: "account_bookings")]

    public function bookings(): Response
    {
        return $this->render('account/bookings.html.twig');
    }
}
