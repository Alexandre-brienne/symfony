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
class AnnonceController extends AbstractController
{
    // #[Route('/', name: 'annonce_index', methods: ['GET'])]
    // public function index(AnnonceRepository $annonceRepository): Response
    // {
    //     return $this->render('annonce/index.html.twig', [
    //         'annonces' => $annonceRepository->findAll(),
    //     ]);
    // }

    #[Route('/new', name: 'annonccce_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $annonce = new Annonce();
        $form = $this->createForm(AnnonceType::class, $annonce);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
    
            $annonce->setDatePublication(new \DateTime());
            $userconnecte = $this->getUser();
            $annonce->setUser($userconnecte);

            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($annonce);
            $entityManager->flush();

            return $this->redirectToRoute('membre_index');
        }

        return $this->render('annonce/new.html.twig', [
            'annonce' => $annonce,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/create/{id}', name: 'annonce_show', methods: ['GET'])]
    public function show(Annonce $annonce): Response
    {
        return $this->render('annonce/show.html.twig', [
            'annonce' => $annonce,
        ]);
    }

    #[Route('/create/{id}/edit', name: 'annonce_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Annonce $annonce): Response
    {
        $form = $this->createForm(AnnonceType::class, $annonce);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //la mise a jour est déclencée automatiquement 
            //car symfony sait déja que l'objet $annonce est associée a une ligne SQL
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('annonce_index');
        }

        return $this->render('annonce/edit.html.twig', [
            'annonce' => $annonce,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/create/{id}', name: 'annonce_delete', methods: ['DELETE'])]
    public function delete(Request $request, Annonce $annonce): Response
    {
        if ($this->isCsrfTokenValid('delete'.$annonce->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($annonce);
            $entityManager->flush();
        }

        return $this->redirectToRoute('annonce_index');
    }
}
