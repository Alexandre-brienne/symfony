<?php

namespace App\Controller;

use App\Entity\Newsletter;
use App\Form\NewsletterType;
use App\Repository\NewsletterRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/newsletter')]
class NewsletterController extends AbstractController
{
    #[Route('/', name: 'newsletter_index', methods: ['GET'])]
    public function index(NewsletterRepository $newsletterRepository): Response
    {   
        //exemple 
        $toto = "coucou";

        //le tuyeau pour mettre du php au twig 
        return $this->render('newsletter/index.html.twig', [
            'newsletters' => $newsletterRepository->findAll(),
            'toto' => $toto,
        ]);
    }

    #[Route('/new', name: 'newsletter_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        // $request est fourni par symfony
        // $request permet de récupérer les infos de formulaire
        $newsletter = new Newsletter(); // code créé avec le make:entity
        // $newsletter est un objet qui va contenir les infos de formulaire
      
           // $newsletter->setNom("nom prérempli");
        // attention : le pré remplisage doit se faire avant createForm
        $form = $this->createForm(NewsletterType::class, $newsletter);
         // connecte le formulaire avec les infos reçues du navigateur
        $form->handleRequest($request);
        //si le formulaire est envoyé et les infos sont correctes
      
        if ($form->isSubmitted() && $form->isValid()) {
                //alors on traite le formulaire 

             // ici on peut compléter les infos manquantes
            $newsletter->setDateInscription(new \DateTime());


            //on envoie les infos en base de données
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($newsletter);
            $entityManager->flush();

            return $this->redirectToRoute('newsletter_index');
        }

        return $this->render('newsletter/new.html.twig', [
            'newsletter' => $newsletter,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'newsletter_show', methods: ['GET'])]
    public function show(Newsletter $newsletter): Response
    {
        return $this->render('newsletter/show.html.twig', [
            'newsletter' => $newsletter,
        ]);
    }

    #[Route('/{id}/edit', name: 'newsletter_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Newsletter $newsletter): Response
    {
        $form = $this->createForm(NewsletterType::class, $newsletter);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('newsletter_index');
        }

        return $this->render('newsletter/edit.html.twig', [
            'newsletter' => $newsletter,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'newsletter_delete', methods: ['DELETE'])]
    public function delete(Request $request, Newsletter $newsletter): Response
    {
        if ($this->isCsrfTokenValid('delete'.$newsletter->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($newsletter);
            $entityManager->flush();
        }

        return $this->redirectToRoute('newsletter_index');
    }
}
