<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class homeController extends AbstractController {


    #[Route('/', name:'homepage')]
    
    public function home() {

        $helloWorld = "Hello Guys !!!";

        return $this->render("home.html.twig",
            [
                "helloWorld" => $helloWorld
            ]
        );
    }

    /**
     * @Route("/hello/{name/age/{age}}", name="hello_page_name_age")
     * @Route("/hello/{name}", name="hello_page_name")
     * @Route("/hello/", name="hello_page")
     */
    public function hello($name = "la famille"): Response
    {
        return new Response("Hello ".$name);
    }
}