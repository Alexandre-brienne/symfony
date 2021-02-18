<?php

namespace App\Controller;
use App\Entity\Newsletter;
use App\Entity\Contact;
use App\Form\Contact1Type;
use App\Form\NewsletterType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
// ne pas oublier les ligne use
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Annonce;
use App\Form\AnnonceType;
use App\Repository\AnnonceRepository;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
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

    #[Route('/annonces', name: 'annonces',  methods: ['GET'])]
    public function annonces(AnnonceRepository $annonceRepository): Response
    {   
        // https://symfony.com/doc/current/doctrine.html#fetching-objects-from-the-database
        // $annonces = $annonceRepository->findAll();   // TROP BASIQUE CAR TRIE PAR id CROISSANT
        $annonces = $annonceRepository->findBy([],["datePublication" => "DESC"]);
        return $this->render('sith/Annonces.html.twig', [
            'controller_name' => 'SiteController',
            'annonces' => $annonces
        ]);
    }

    #[Route('/annonce/{slug}/{id}', name: 'annonce', methods: ['GET'])]
    public function show(Annonce $annonce): Response
    {
        return $this->render('sith/annonce.html.twig', [
            'annonce' => $annonce,
        ]);
    }

    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route('/recherche', name: 'recherche')]
    public function recherche(Request $request,AnnonceRepository $annonceRepository): Response
    {
        //recuperer le mot recherché 
        // et lancé une requete sql pour chercher les annonces 
        //dont le tite contient le mot clé 
        $mot = $request->get('mot');
        dump($mot);
        if (!empty($mot)){

                // on va lancer la recherche sur le mot
            // recherche exacte
            // $annonces = $annonceRepository->findBy([
            //     "titre" => $mot,
            // ], [ "datePublication" => "DESC"]);

            // si on veut que le titre puisse avoir un texte en plus que le mot cherché
            // => SQL LIKE 
            // https://sql.sh/cours/where/like
            $annonces = $annonceRepository->chercherMot($mot);
        }

        return $this->render('sith/recherche.html.twig', [
            'annonces' => $annonces ?? [] ,
        ]);
    }
    
}
