<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\AdminCommentType;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminCommentController extends AbstractController
{
    #[Route('/admin/comments', name: 'admin_comment_index')]

    public function index(CommentRepository $repo): Response
    {
        $comments = $repo->findAll();

        return $this->render('admin/comment/index.html.twig', [

            'comments' => $comments

        ]);
    }

    #[Route("/admin/comments/{id}/edit", name: "admin_comment_edit")]

    public function edit(Comment $comment, Request $request, EntityManagerInterface $em) {

        $form = $this->createForm(AdminCommentType::class, $comment);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $em->persist($comment);

            $em->flush();

            $this->addFlash(

                "alert alert-success",

                "Le commentaire n° {$comment->getId()} a bien été modifié !"

            );
        }

        return $this->render("admin/comment/edit.html.twig", [
            
            "comment" => $comment,

            "form" => $form->createView()
        ]);

    }

   #[Route('admin/comments/{id}/delete', name: "admin_comment_delete")]

    public function delete(Comment $comment, EntityManagerInterface $em): Response
    {
        $em->remove($comment);

        $em->flush();

        $this->addFlash(
           'alert alert-success',

           "Le commentaire de <strong>{$comment->getAuthor()->getFullName()}</strong> a bien été supprimé !"
        );


        return $this->redirectToRoute('admin_comment_index');
    }
}
