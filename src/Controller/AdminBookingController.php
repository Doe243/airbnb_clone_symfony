<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Form\BookingType;
use App\Form\AdminBookingType;
use App\Repository\BookingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminBookingController extends AbstractController
{
    #[Route('/admin/bookings', name: 'admin_booking_index')]

    public function index(BookingRepository $repo): Response
    {

        $bookings = $repo->findAll();

        return $this->render('admin/booking/index.html.twig', [

            "bookings" => $bookings
        ]);
    }

   #[Route("/admin/bookings/{id}/edit", name: "admin_booking_edit")]

    public function edit(Booking $booking, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(AdminBookingType::class, $booking);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //1 ere Méthode, on calcul manuellement => $booking->setAmount($booking->getAd()->getPrice() * $booking->getDuration());
            //2ème méthode, on utilise la life cycle de doctrine grâce à la fonction getDuration
            $booking->setAmount(0);
            
            $em->persist($booking);

            $em->flush();

            $this->addFlash(

                'alert alert-success',

                "La réservation n° {$booking->getId()} a bien été modifier !"

            );

            return $this->redirectToRoute('admin_booking_index');
        }

        return $this->render('admin/booking/edit.html.twig', [

            "booking" => $booking,

            "form" => $form->createView()
        ]);
    }


    #[Route("/admin/bookings/{id}/delete", name: "admin_booking_delete")]

    public function delete(Booking $booking, EntityManagerInterface $em): Response
    {

        $em->remove($booking);

        $em->flush();

        $this->addFlash(

            "alert alert-success",

            "La réservation de {$booking->getBooker()->getFullName()} a bien été supprimer !"
        );

        return $this->redirectToRoute("admin_booking_index");
    }
}
