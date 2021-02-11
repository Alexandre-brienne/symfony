<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SithController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('sith/index.html.twig', [
            'controller_name' => 'SithController',
        ]);
    }
    #[Route('/galerie', name: 'galerie')]
    public function jedi(): Response
    {
        return $this->render('sith/galerie.html.twig', [
            'controller_name' => 'SithController',
        ]);
    }
    #[Route('/contact', name: 'contact')]
    public function contact(): Response
    {
        return $this->render('sith/contact.html.twig', [
            'controller_name' => 'SithController',
        ]);
    }
}
