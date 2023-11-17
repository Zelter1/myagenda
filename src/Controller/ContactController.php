<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\NouveauContactType;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/', name: 'contact')]
    public function index(ContactRepository $contactRepository): Response
    {
        $contacts = $contactRepository->findAll();
        return $this->render('contact/index.html.twig', [
            'contacts' => $contacts
        ]);
    }

    #[Route('/contact/{id}', name: 'details_contact')]
    public function details(ContactRepository $contactRepository, int $id): Response
    {
        $contact = $contactRepository->find($id);

        if (!$contact) {
            throw $this->createNotFoundException(
                'No product found for id '.$id
            );
        }
        return $this->render('contact/detail.html.twig', [
            'contact' => $contact
        ]);
    }

    #[Route('/nouveau', name: 'nouveau_contact')]
    public function nouveau(Request $request, EntityManagerInterface $em):Response
    {
       // On crée un nouveau contac
        $contact = new Contact();

        // On crée le formulaire
        $contactForm = $this->createForm(NouveauContactType::class, $contact);

        // On traite la requête du formulaire
        $contactForm->handleRequest($request);

        //On vérifie si le formulaire est soumis ET valide
        if($contactForm->isSubmitted() && $contactForm->isValid())
        {
            $em->persist($contact);
            $em->flush();

            $this->addFlash('success', 'Annonce ajoutée avec succès');
            return $this->redirectToRoute('contact');
        }

        return $this->render('contact/nouveau.html.twig',[
           'contact' => $contactForm->createView()
        ]);
    }

    #[Route('/contact/supprimer/{id}', name: 'supprimer_contact')]
    public function supprimer(ManagerRegistry $doctrine, $id)
    {
        $contact = $doctrine->getRepository(Contact::class)->find($id);
        $entityManager = $doctrine->getManager();
        $entityManager->remove($contact);
        $entityManager->flush();
        return $this->redirectToRoute('contact');

    } 

    #[Route('/contact/modifier/{id}', name:'modifier_contact')]
    public function modifContact(ManagerRegistry $doctrine, Request $request, $id)
    {
        $entityManager = $doctrine->getManager();
        $contact = $doctrine->getRepository(Contact::class)->find($id);
        $formContact = $this->createForm(NouveauContactType::class, $contact);
        
        $formContact->handleRequest($request);
        if($formContact->isSubmitted() && $formContact->isValid())
        {
            $entityManager->flush();

            $this->addFlash('success', "Le contact a bien été modifiée");

            return $this->redirectToRoute('contact');
        }
       return $this->render('contact/nouveau.html.twig',[
        'contact' => $formContact->createView()
       ]);
    }

}
