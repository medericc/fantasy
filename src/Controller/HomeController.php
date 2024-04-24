<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        dd($this->getUser());
        if ($this->getUser()) {
            return $this->render('/index.html.twig', [
                'controller_name' => 'HomeController',
            ]);
            
        } else {
            // return $this->render('/invite.html.twig', [
            //     'controller_name' => 'HomeController',
            // ]);
            
        }
    }
}
