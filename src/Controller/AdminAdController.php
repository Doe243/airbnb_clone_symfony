<?php

namespace App\Controller;

use App\Entity\Ad;
use App\Form\AdType;
use App\Repository\AdRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminAdController extends AbstractController
{
    #[Route('/admin/ads', name: 'admin_ads_index')]

    public function index(AdRepository $repo): Response
    {
        $ads = $repo->findAll();

        return $this->render('admin/ad/index.html.twig', [

            'ads' => $ads,
        ]);
    }


    #[Route('/admin/ads/{id}/edit', name: "admin_ads_edit")]

    public function edit(Ad $ad, Request $request, EntityManagerInterface $em) {
        
        $form = $this->createForm(AdType::class, $ad);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $em->persist($ad);

            $em->flush();
            
            $this->addFlash(
                
                'alert alert-success',
                "L'annonce <strong>{$ad->getTitle()} a bien été enregister !"
                
            );
        }

        return $this->render('admin/ad/edit.html.twig', [

            'ad' => $ad,

            'form' => $form->createView()

        ]);
    }

    #[Route('/admin/ads/{id}/delete', name:"admin_ads_delete")]

    public function delete(Ad $ad, EntityManagerInterface $em) {

        if (count($ad->getBookings()) > 0) {
            
            $this->addFlash(
                "alert alert-warning",
                "Vous ne pouvez pas supprimer l'annonce <strong>{$ad->getTitle()}</strong> parce qu'elle contient déjà des réservations !"

            );
        } else {           
                    $em->remove($ad);
            
                    $em->flush();
            
                    $this->addFlash(
                        "alter alert-success",
            
                        "L'annonce <strong>{$ad->getTitle()}</strong> a bien été supprimé !"
            
                    );
        }

        return $this->redirectToRoute('admin_ads_index');
    }

}
