<?php

namespace App\Controller;
use App\Entity\Annonce;
use App\Form\AnnonceType;
use App\Repository\AnnonceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/membre')]
class MembreController extends AbstractController
{
    #[Route('/', name: 'membre_index',methods: ['GET'])]
    public function index(AnnonceRepository $annonceRepository): Response
    {
        return $this->render('membre/index.html.twig', [
            'controller_name' => 'MembreController',
            'annonces' => $annonceRepository->findAll(),
        ]);
    }

    #[Route('/create', name: 'membre_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $annonce = new Annonce();
        $form = $this->createForm(AnnonceType::class, $annonce);
        $form->handleRequest($request);
        $messageConfirmation = "";
        if ($form->isSubmitted() && $form->isValid()) {
    
            $annonce->setDatePublication(new \DateTime());
            $userconnecte = $this->getUser();
            $annonce->setUser($userconnecte);

            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($annonce);
            $entityManager->flush();

            $messageConfirmation = "votre annonce est publiée";
        }

        return $this->render('membre/membre.html.twig', [
            'annonce' => $annonce,
            'form' => $form->createView(),
            'messageConfirmation' => $messageConfirmation ?? "",
        ]);
    }
    
}
