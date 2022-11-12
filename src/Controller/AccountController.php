<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AccountType;
use App\Entity\PasswordUpdate;
use App\Form\RegistrationType;
use App\Form\PasswordUpdateType;
use Symfony\Component\Form\FormError;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AccountController extends AbstractController
{
    /**
     * Permet aux utilisateurs de se connecter
     *
     * @return Response
     */

    #[Route('/login', name: 'account_login')]

    public function login(AuthenticationUtils $utils): Response
    {
        $error = $utils->getLastAuthenticationError();

        $username = $utils->getLAstUsername();
    
        return $this->render('account/login.html.twig', [

            // last authentication error (if any)
            'hasError' => $error !== null,

            // last username entered by the user (if any)
            'username' => $username
        ]);
    }

    /**
     * Permet aux utilisateurs de se déconnecter
     *
     * @return void
     */
    #[Route('/logout', name: 'account_logout')]

    public function logout(): void {}

    /**
     * Permet d'afficher le formulaire d'inscription
     */

    #[Route('/register', name: 'account_register')]

    public function register(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $encoder ): Response
    {

        $user = new User();

        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $hash = $encoder->hashPassword($user, $user->getHash());

            $user->setHash($hash);

            $em->persist($user);

            $em->flush();

            $this->addFlash(
                'alert-success',
                "Votre compte a bien été créer, vous pouvez maintenant vous connecter !"
            );

            return $this->redirectToRoute("account_login");

        }
        
        return $this->render('account/registration.html.twig', [

            "form" => $form->createView()
        ]);
    }

    /**
     * Permet d'afficher et de traiter le formulaire de modification de profil
     * @return Response
     */

    #[Route('/account/profile', name: 'account_profile')]
    #[IsGranted('ROLE_USER')]

    public function profile(Request $request, EntityManagerInterface $em ): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(AccountType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $em->persist($user);

            $em->flush();

            $this->addFlash(
                'alert-success',
                "Votre profile a bien été modifier !"
            );

            ///return $this->redirectToRoute("account_login");
        }

        
        return $this->render('account/profile.html.twig', [

            'form' => $form->createView()
        ]);
    }

    /**
     * Permet de modifierr le mot de passe
     * 
     * @return Response
     */

    #[Route('/account/update-password', name: 'account_update_password')]
    #[IsGranted("ROLE_USER")]
    
    public function updatePassword(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $encoder): Response
    {
        $passwordUpdate = new PasswordUpdate();

        $user = $this->getUser();

        $form = $this->createForm(PasswordUpdateType::class, $passwordUpdate);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            if (!password_verify($passwordUpdate->getOldPassword(), $user->getHash())) {
                //Gerer l'erreur en cas de mauvais mot de passe (oldPassword)

                $form->get('oldPassword')->addError(new FormError("Le mot de passe que vous avez tapé n'est votre mot de passe actuel !"));
            }
            else {
                $newPassword = $passwordUpdate->getNewPassword();

                $hash = $encoder->hashPassword($user, $newPassword);

                $user->setHash($hash);

                $em->persist($user);
                
                $em->flush();

                $this->addFlash(
                    'alert-success',
                    "Votre mot de passe a bien été modifié !"
                );

                return $this->redirectToRoute('homepage');

            }

        }
        
        return $this->render('account/update_password.html.twig', [

            'form' => $form->createView()
        ]);
    }

    /**
     * Permet de voir le profil de l'utilisateur connecté
     */

    #[Route('/account', name: 'account_index')]
    #[IsGranted("ROLE_USER")]

    public function myAccount(): Response
    {
        $user = $this->getUser();

        return $this->render('/user/index.html.twig', [
            'user' => $user
        ]);
    }
}
