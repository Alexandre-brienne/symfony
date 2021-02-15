<?php

namespace App\Controller;
use App\Entity\Newsletter;
use App\Entity\Contact;
use App\Form\Contact1Type;
use App\Form\NewsletterType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
// ne pas oublier les ligne usa
use Symfony\Component\HttpFoundation\Request;
class SithController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
       
        $newsletter = new Newsletter();
        $form = $this->createForm(NewsletterType::class, $newsletter);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            //alors on traite le formulaire 

         // ici on peut compléter les infos manquantes
        $newsletter->setDateInscription(new \DateTime());


        //on envoie les infos en base de données
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($newsletter);
        $entityManager->flush();
      
        // pas de redirection pour la page d'accueil
        // return $this->redirectToRoute('newsletter_index');
    }
        return $this->render('sith/index.html.twig', [
            
            'controller_name' => 'SithController',
            'newsletter' => $newsletter,
            'form' => $form->createView(),
        ]);
    }
    #[Route('/galerie', name: 'galerie')]
    public function jedi(): Response
    {
        return $this->render('sith/galerie.html.twig', [
            'controller_name' => 'SithController',
        ]);
    }
    #[Route('/contact', name: 'contact', methods: ['GET', 'POST'])]
    public function contact(Request $request): Response
    {
        $messageConfirmation = 'merci de remplir le formulaire';
        $class ="red";
        $contact = new Contact();
        $form = $this->createForm(Contact1Type::class, $contact);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $contact->setDatemessage(new \DateTime());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($contact);
            $entityManager->flush();
            $messageConfirmation = 'merci de votre inscription';
            $class ="vert";

        }
        return $this->render('sith/contact.html.twig', [
            'controller_name' => 'SithController',
            'contact' => $contact,
            'form' => $form->createView(),
            'messageConfirmation'   => $messageConfirmation,
            "class" => $class,
        ]);
    }
}
