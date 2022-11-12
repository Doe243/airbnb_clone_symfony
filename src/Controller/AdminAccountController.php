<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AdminAccountController extends AbstractController
{
    #[Route('/admin/login', name: 'admin_account_login')]

    public function login(AuthenticationUtils $utils): Response
    {
        $error = $utils->getLastAuthenticationError();

       /*  $username = $utils->getLAstUsername(); */

        return $this->render('admin/account/login.html.twig', [

            // last authentication error (if any)
            'hasError' => $error !== null,

            // last username entered by the user (if any)
           /*  'username' => $username */
        ]);
    }
    /**
     * Permet aux utilisateurs de se déconnecter
     *
     * @return void
     */
    #[Route("/admin/logout", name: 'admin_account_logout')]

    public function logout(): void {}

   
}
