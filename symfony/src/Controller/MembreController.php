<?php

namespace App\Controller;
use App\Entity\Annonce;
use App\Form\AnnonceType;
use App\Repository\AnnonceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
#[Route('/membre')]
class MembreController extends AbstractController
{


    #[Route('/create', name: 'membre_new', methods: ['GET', 'POST'])]
    public function new(Request $request, SluggerInterface $slugger): Response
    {
        $annonce = new Annonce();
        $form = $this->createForm(AnnonceType::class, $annonce);
        $form->handleRequest($request);
        $messageConfirmation = "";
        if ($form->isSubmitted() && $form->isValid()) {
    
            $annonce->setDatePublication(new \DateTime());
            $userconnecte = $this->getUser();
            $annonce->setUser($userconnecte);

            
            // code de gestion de upload image
            $imageFile = $form->get('image')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'imageFilename' property to store the PDF file name
                // instead of its contents
                $annonce->setImage($newFilename);
            }else {
                $annonce->setImage('');
            }



            
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
    
    #[Route('/', name: 'membre_index',methods: ['GET'])]
    public function index(AnnonceRepository $annonceRepository): Response
    {
        $userConnecte = $this->getUser();
        return $this->render('membre/index.html.twig', [
            'controller_name' => 'MembreController',
            'annonces' => $annonceRepository->findby([
                "user" => $userConnecte 
            ]),
        ]);
    }

    #[Route('/{id}', name: 'membre_show', methods: ['GET'])]
    public function show(Annonce $annonce): Response
    {
        return $this->render('annonce/show.html.twig', [
            'annonce' => $annonce,
        ]);
    }
}
