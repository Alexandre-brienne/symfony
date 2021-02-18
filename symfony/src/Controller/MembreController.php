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
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

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
            } else {
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

    #[Route('/', name: 'membre_index', methods: ['GET'])]
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

    #[Route('/{id}', name: 'membre_delete', methods: ['DELETE'])]
    public function delete(Request $request, Annonce $annonce): Response
    {
        if ($this->isCsrfTokenValid('delete' . $annonce->getId(), $request->request->get('_token'))) {
            //verifier que l'anonce appartient a l 'utilisateur connecté 
            $userConnecte = $this->getUser();
            $auteurAnnonce = $annonce->getUser();
            if ($userConnecte != null && $auteurAnnonce != null) {
                if ($userConnecte->getid() == $auteurAnnonce->getid()) {
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->remove($annonce);
                    $entityManager->flush();

                    // il faudrait aussi suprprimer le fichier image 

                    $dossierUpload = $this->getparameter('images_directory');
                    $fichierImage = "$dossierUpload/" . $annonce->getImage();
                    if (is_file($fichierImage)) {
                        unlink($fichierImage);
                    }
                }
            }
            //declenche le delete de la ligne

        }

        return $this->redirectToRoute('membre_index');
    }


    #[Route('/{id}/edit', name: 'membre_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Annonce $annonce, SluggerInterface $slugger): Response
    {
        $userConnecte = $this->getUser();
        $auteurAnnonce = $annonce->getUser();
        if ($userConnecte != null && $auteurAnnonce != null) {
            if ($userConnecte->getid() == $auteurAnnonce->getid()) {

                $form = $this->createForm(AnnonceType::class, $annonce);
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {

                    // code de gestion de upload image
                    $imageFile = $form->get('image')->getData();

                    // this condition is needed because the 'brochure' field is not required
                    // so the PDF file must be processed only when a file is uploaded
                    if ($imageFile) {
                        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                        // this is needed to safely include the file name as part of the URL
                        $safeFilename = $slugger->slug($originalFilename);
                        $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                        // Move the file to the directory where brochures are stored
                        try {

                            $imageFile->move(
                                $this->getParameter('images_directory'),
                                $newFilename
                            );
                        } catch (FileException $e) {
                            // ... handle exception if something happens during file upload
                        }
                        //supprimer l'image d'avant 

                        $dossierUpload = $this->getparameter('images_directory');
                        $fichierImage = "$dossierUpload/" . $annonce->getImage();
                        if (is_file($fichierImage)) {
                        unlink($fichierImage);
                        }
                        // updates the 'imageFilename' property to store the PDF file name
                        // instead of its contents
                        $annonce->setImage($newFilename);
                    } else {
                        $annonce->setImage('');
                    }
                    //la mise a jour est déclencée automatiquement 
                    //car symfony sait déja que l'objet $annonce est associée a une ligne SQL
                    $this->getDoctrine()->getManager()->flush();

                    return $this->redirectToRoute('membre_index');
                }

                return $this->render('membre/edit.html.twig', [
                    'annonce' => $annonce,
                    'form' => $form->createView(),
                ]);
            } else {
                return $this->redirectToRoute('membre_index');
            }
        } else {
            return $this->redirectToRoute('membre_index');
        }
    }
}
