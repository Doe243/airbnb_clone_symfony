<?php

/*
 * This file is the paging service developed with love 😊.
 *
 * (c) René MUMBA <renemumba@gmail.com>
 *
 * Use it as much as you want, it's free
 * No need for a license.
 */

namespace App\Service;

use Exception;
use Twig\Environment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class PaginationService {


    /**
     * Declaration of all our variables
     *
     * @author René MUMBA <renemumba@gmail.com>
     */

    private $entityClass;

    private $limit = 10;

    private $currentPage = 1;

    private $manager;

    private $twig;

    private $route;

    private $templatePath;

    

    public function __construct(EntityManagerInterface $manager, Environment $twig, RequestStack $request, $templatePath)
    {
        $this->route = $request->getCurrentRequest()->attributes->get('_route');

        $this->manager = $manager;

        $this->twig = $twig;

        $this->templatePath = $templatePath;
    }

    public function setTemplatePath($templatePath) {

        $this->templatePath = $templatePath;

        return $this;

    }

    public function getTemplatepath() {

        return $this->templatePath;
    }

    public function setRoute($route) {
        
        $this->route = $route;

        return $this;
    }

    public function getRoute() {

        return $this->route;
    }


    public function display() {

        $this->twig->display($this->templatePath, [

            'page' => $this->currentPage,

            'pages' => $this->getPages(),

            'route' => $this->route
        ]);
    }

    public function getPages() {

        if (empty($this->entityClass)) {
            
            throw new Exception("Vous n'avez pas spécifié l'entité sur laquelle nous devons paginer ! Utilser la méthode setEntityClass() de votre objet PaginationService !", 1);
            
        }

        $repo = $this->manager->getRepository($this->entityClass);

        $total = count($repo->findAll());

        $pages = ceil($total / $this->limit);

        return $pages;
    }

    public function getData() {

        if (empty($this->entityClass)) {
            
            throw new Exception("Vous n'avez pas spécifié l'entité sur laquelle nous devons paginer ! Utilser la méthode setEntityClass() de votre objet PaginationService !", 1);
            
        }

        $offset = $this->currentPage * $this->limit - $this->limit;

        $repo = $this->manager->getRepository($this->entityClass);

        $data = $repo->findBy([], [], $this->limit, $offset);

        return $data;
    }

    public function setCurrentPage($currentPage) {
        
        $this->currentPage = $currentPage;

        return $this;
    }

    public function getCurrentPage() {

        return $this->currentPage;
    }

    public function setLimit($limit) {
        
        $this->limit = $limit;

        return $this;
    }

    public function getLimit() {

        return $this->limit;
    }

    public function setEntityClass($entityClass) {

        $this->entityClass = $entityClass;

        return $this;
    }

    public function getEntityClass() {

        return $this->entityClass;
    }
}