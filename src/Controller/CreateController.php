<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\CreateType;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
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
        $user = $this->security->getUser();

        if ($user instanceof Participant)
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
            'sortieForm' => $sortieForm->createView(),
            "sortie" => $sortie
        ]);
    }

    #[Route(path: '/modification/{id}', name: 'modification')]
    public function modification(int $id, Request $request, EntityManagerInterface $entityManager, SortieRepository $sortieRepository): Response
    {
        $sortie = $sortieRepository->find($id);
        if ($sortie != null) {

            $sortieForm = $this->createForm(CreateType::class, $sortie);

            $sortieForm->handleRequest($request);

            if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
                $entityManager->persist($sortie);
                $entityManager->flush();

                $this->addFlash('success', 'Modification effectuée !');
                return $this->redirectToRoute('sorties_list');
            }
        }
        return $this->render('sorties/create.html.twig', [
            'sortieForm' => $sortieForm->createView(),
            "sortie" => $sortie
        ]);
    }

    #[Route('/annulation/{id}', name: 'annulation')]
    public function annulation(int $id,
                               Request $request,
                               EntityManagerInterface $entityManager,
                               SortieRepository $sortieRepository,
                               EtatRepository $etatRepository): Response
    {
        $sortie = $sortieRepository->find($id);
        $prevInfo = 'Description initiale : '.$sortie->getInfosSortie();
        $sortieForm = $this->createForm(CreateType::class, $sortie);
        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $sortie->setEtat($etatRepository->findOneBy(['libelle' => "annulée"]));
            $sortie->setInfosSortie('Sortie annulée pour le motif suivant : '.$sortieForm["infosSortie"]->getData(). ' '. $prevInfo);
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash('success', 'Annulation effectuée !');
            return $this->redirectToRoute('create_modification', ['id' => $id]);
        }
        return $this->render('sorties/annulation.html.twig', [
            'sortieForm' => $sortieForm->createView(),
            "sortie" => $sortie
        ]);
    }
}