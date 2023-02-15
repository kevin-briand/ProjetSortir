<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\CreateType;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use App\Security\SortieVoter;
use App\Workflow\EtatWorkflow;
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
    public function create(Request $request,
                           EntityManagerInterface $entityManager,
                           EtatRepository $etatRepository,
                           EtatWorkflow $etatWorkflow): Response
    {
        $sortie = new Sortie();
        $this->denyAccessUnlessGranted(SortieVoter::VIEW, $sortie);

        /* @var Participant $user */
        $user = $this->security->getUser();

        $sortie->setCampus($user->getCampus());
        $sortie->setOrganisateur($user);
        $sortieForm = $this->createForm(CreateType::class, $sortie);
        $sortie->setEtat($etatRepository->findOneBy(['libelle' => Etat::CREATION]));
        $sortie->addParticipant($user);

        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $entityManager->persist($sortie);
            $entityManager->flush();

            if($request->get('submit') == 'publier') {
                $etatWorkflow->setEtat($sortie, Etat::TRANS_PUBLICATION);
            }

            $this->addFlash('success', 'Sortie ajoutée !');
            return $this->redirectToRoute('sorties_list');
        }


        return $this->render('sorties/create.html.twig', [
            'sortieForm' => $sortieForm->createView(),
            "sortie" => $sortie
        ]);
    }

    #[Route(path: '/modification/{id}', name: 'modification')]
    public function modification(int $id,
                                 Request $request,
                                 Security $security,
                                 EntityManagerInterface $entityManager,
                                 EtatWorkflow $etatWorkflow,
                                 SortieRepository $sortieRepository): Response
    {
        $user = $security->getUser();
        $sortie = $sortieRepository->find($id);

        $this->denyAccessUnlessGranted(SortieVoter::EDIT, $sortie);

        $sortieForm = $this->createForm(CreateType::class, $sortie);

        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $entityManager->persist($sortie);
            $entityManager->flush();

            if($request->get('submit') == 'publier')
                $etatWorkflow->setEtat($sortie,Etat::TRANS_PUBLICATION);

            $this->addFlash('success', 'Modification effectuée !');
            return $this->redirectToRoute('sorties_list');
        }

        return $this->render('sorties/create.html.twig', [
                'sortieForm' => $sortieForm->createView(),
                "sortie" => $sortie
            ]);
    }

    #[Route('/annulation/{id}', name: 'annulation')]
    public function annulation(int $id,
                               Request $request,
                               Security $security,
                               EntityManagerInterface $entityManager,
                               SortieRepository $sortieRepository,
                               EtatWorkflow $etatWorkflow): Response
    {
        $user = $security->getUser();
        $sortie = $sortieRepository->find($id);
      
        $this->denyAccessUnlessGranted(SortieVoter::REMOVE, $sortie);

        $prevInfo = 'Description initiale : '.$sortie->getInfosSortie();
        $sortieForm = $this->createForm(CreateType::class, $sortie);
        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            // Suppression de la sortie si en cours de création
            if ($etatWorkflow->getEtat($sortie) == Etat::CREATION) {
                $entityManager->remove($sortie);
            } else {
                $etatWorkflow->setEtat($sortie, Etat::TRANS_ANNULATION);
                $sortie->setInfosSortie('Sortie annulée pour le motif suivant : '.$sortieForm["infosSortie"]->getData(). ' '. $prevInfo);
                $entityManager->persist($sortie);
                $entityManager->flush();
            }

            $this->addFlash('success', 'Annulation effectuée !');
            return $this->redirectToRoute('sorties_details', ['id' => $id]);
        }
        return $this->render('sorties/annulation.html.twig', [
            'sortieForm' => $sortieForm->createView(),
            "sortie" => $sortie
        ]);
    }
}