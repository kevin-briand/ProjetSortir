<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\CreateType;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use App\Security\SortieVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/create', name: 'create_')]
class CreateController extends AbstractController
{
    public function __construct(private Security $security)
    {
    }

    #[Route(path: '', name: 'sortie')]
    public function create(Request $request, EntityManagerInterface $entityManager, EtatRepository $etatRepository): Response
    {
        $sortie = new Sortie();
        $this->denyAccessUnlessGranted(SortieVoter::VIEW, $sortie);

        /* @var Participant $user */
        $user = $this->security->getUser();

        $sortie->setCampus($user->getCampus());
        $sortie->setOrganisateur($user);
        $sortieForm = $this->createForm(CreateType::class, $sortie);
        $sortie->setEtat($etatRepository->findOneBy(['libelle' => "création"]));
        $sortie->addParticipant($user);

        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Sortie ajoutée !');
            return $this->redirectToRoute('sorties_list');
        }


        return $this->render('sorties/create.html.twig', [
            'sortieForm' => $sortieForm->createView()
        ]);
    }

    #[Route(path: '/modification/{id}', name: 'modification')]
    public function modification(int $id, Request $request, EntityManagerInterface $entityManager, SortieRepository $sortieRepository): Response
    {
        $sortie = $sortieRepository->find($id);
        $this->denyAccessUnlessGranted(SortieVoter::EDIT, $sortie);

        $sortieForm = $this->createForm(CreateType::class, $sortie);

        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Modification effectuée !');
            return $this->redirectToRoute('sorties_list');
        }

        return $this->render('sorties/create.html.twig', [
            'sortieForm' => $sortieForm->createView()
        ]);
    }
}