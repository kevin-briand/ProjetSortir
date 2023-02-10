<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Form\FilterType;
use App\Entity\Participant;
use App\Repository\SortieRepository;
use App\Workflow\EtatWorkflow;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;

#[Route('/sorties', name: 'sorties_')]
class SortiesController extends AbstractController
{

    #[Route('/', name: 'list')]
    public function list(SortieRepository $sortieRepository,
                         Request $request, UserInterface $user,
                         EtatWorkflow $etatWorkflow): Response
    {
        $sortiesFilter = $this->createForm(FilterType::class);
        $sortiesFilter->handleRequest($request);

        if($sortiesFilter->isSubmitted() && $sortiesFilter->isValid())
        {
           // $campus = $sortiesFilter->get('campus')->getData();
            $usrID = $user->getId();
            $datas = $sortiesFilter->getData();
           //dd($datas);

            $sorties = $sortieRepository->filterBy($datas, $usrID);
        }else{
            $sorties = $sortieRepository->findAllSorties();
        }


        //Changement des états si nécessaire
        $etatWorkflow->controleEtat($sorties);

        return $this->render('sorties/sorties.html.twig', [
            "sorties" => $sorties,
            "sortiesFilter" => $sortiesFilter
        ]);
    }

    #[Route('/inscription/', name: 'inscription')]
    public function inscription(Request $request,
                                SortieRepository $sortieRepository,
                                EntityManagerInterface $entityManager,
                                Security $security,
                                EtatWorkflow $etatWorkflow): JsonResponse
    {
        $json = array();

        if(!$request->isXmlHttpRequest()) {
            $json['error'] = "Bad request";
            return new JsonResponse($json);
        }

        /* @var Participant $user */
        $user = $security->getUser();
        $sortie = $sortieRepository->find($request->request->get('id'));

        if (!$user)
            $json['error'] = "Vous n'êtes pas connecté !";
        if (!$sortie)
            $json['error'] = "L'inscription à la sortie ".$sortie->getNom()." à échoué ! (sortie non trouvé)";
        elseif ($etatWorkflow->getEtat($sortie) !== Etat::OUVERTE)
            $json['error'] = "L'inscription à la sortie ".$sortie->getNom()." à échoué ! (la sortie est ".$etatWorkflow->getEtatName($sortie).")";
        elseif ($sortie->getParticipants()->count() >= $sortie->getNbInscriptionsMax())
            $json['error'] = "L'inscription à la sortie ".$sortie->getNom()." à échoué ! (nombre max de participants atteint)";
        else {
            if ($sortie->getParticipants()->contains($user)) {
                $json['error'] = "L'inscription à la sortie ".$sortie->getNom()." à échoué ! (vous participez déjà à la sortie)";
            } else {
                $sortie->addParticipant($user);
                //Test si sortie pleine
                if($sortie->getParticipants()->count() == $sortie->getNbInscriptionsMax()) {
                    $etatWorkflow->setEtat($sortie, Etat::TRANS_CLOTURE);
                }
                $entityManager->persist($sortie);
                $entityManager->flush();
                $json['info'] = "Inscription à la sortie ".$sortie->getNom()." réussie !";
            }
        }
        return new JsonResponse($json);
    }

    #[Route('/desistement/', name: 'desistement')]
    public function desistement(Request $request,
                                SortieRepository $sortieRepository,
                                EntityManagerInterface $entityManager,
                                Security $security,
                                EtatWorkflow $etatWorkflow): JsonResponse
    {
        $json = array();

        if (!$request->isXmlHttpRequest()) {
            $json['error'] = "Bad request";
            return new JsonResponse($json);
        }

        $user = $security->getUser();
        $sortie = $sortieRepository->find($request->request->get('id'));

        if (!$user)
            $json['error'] = "Vous n'êtes pas connecté !";
        if (!$sortie)
            $json['error'] = "Le désistement à la sortie à " . $sortie->getNom() . " échoué ! (sortie non trouvé)";
        else {
            if ($user instanceof Participant) {
                if (!$sortie->getParticipants()->contains($user)) {
                    $json['error'] = "le désistement à la sortie à " . $sortie->getNom() . " échoué ! (vous ne participez pas à la sortie)";
                } elseif ($etatWorkflow->getEtat($sortie) !== Etat::EN_COURS &&
                          $etatWorkflow->getEtat($sortie) !== Etat::CLOTUREE) {
                    $json['error'] = "le désistement à la sortie à " . $sortie->getNom() . " échoué ! (la sortie ne peux pas être modifiée)";
                } else {
                    $sortie->removeParticipant($user);
                    //Changement d'état si la date le permet
                    if ($sortie->getDateLimiteInscription() > new Date()) {
                        $etatWorkflow->setEtat($sortie,Etat::TRANS_REOUVERTURE);
                    }
                    $entityManager->persist($sortie);
                    $entityManager->flush();
                    $json['info'] = "Désinscription à la sortie " . $sortie->getNom() . " réussie !";
                }
            }
        }
        return new JsonResponse($json);
    }

    #[Route('/details/{id}', name: 'details')]
    public function details(int $id, SortieRepository $sortieRepository): Response
    {
        $sortie = $sortieRepository->find($id);

        return $this->render('sorties/details.html.twig', [
            "sortie" => $sortie
        ]);
    }

    #[Route('/publier/', name: 'publier')]
    public function publier(SortieRepository $sortieRepository,
                            Request $request,
                            Security $security,
                            EtatWorkflow $etatWorkflow): JsonResponse
    {
        $json = array();

        if (!$request->isXmlHttpRequest()) {
            $json['error'] = "Bad request";
            return new JsonResponse($json);
        }

        $user = $security->getUser();
        $sortie = $sortieRepository->find($request->request->get('id'));

        if (!$user)
            $json['error'] = "Vous n'êtes pas connecté !";
        if (!$sortie)
            $json['error'] = "Le désistement à la sortie à " . $sortie->getNom() . " échoué ! (sortie non trouvé)";
        else {
            if ($etatWorkflow->canTransition($sortie, Etat::TRANS_PUBLICATION) &&
                $sortie->getOrganisateur() === $user) {
                $etatWorkflow->setEtat($sortie, Etat::TRANS_PUBLICATION);
                $json['info'] = "La sortie " . $sortie->getNom() . " est ouverte à tous !";
            } else {
                $json['error'] = "La sortie " . $sortie->getNom() . " n'a pas pu être modifié";
            }
        }
        return new JsonResponse($json);
    }

    #[Route('/annuler/', name: 'annuler')]
    public function annuler(SortieRepository $sortieRepository,
                            Request $request,
                            Security $security,
                            EtatWorkflow $etatWorkflow,
                            EntityManager $entityManager): JsonResponse
    {
        $json = array();

        if (!$request->isXmlHttpRequest()) {
            $json['error'] = "Bad request";
            return new JsonResponse($json);
        }

        $user = $security->getUser();
        $sortie = $sortieRepository->find($request->request->get('id'));

        if (!$user)
            $json['error'] = "Vous n'êtes pas connecté !";
        if (!$sortie)
            $json['error'] = "Le désistement à la sortie à " . $sortie->getNom() . " échoué ! (sortie non trouvé)";
        else {
            if ($etatWorkflow->canTransition($sortie, Etat::TRANS_ANNULATION) &&
                $sortie->getOrganisateur() === $user) {
                if ($etatWorkflow->getEtat($sortie) == Etat::CREATION) {
                    $entityManager->remove($sortie);
                } else {
                    $etatWorkflow->setEtat($sortie, Etat::TRANS_ANNULATION);
                }
                $json['info'] = "La sortie " . $sortie->getNom() . " à été annulée !";
            } else {
                $json['error'] = "La sortie " . $sortie->getNom() . " n'a pas pu être annulée";
            }
        }
        return new JsonResponse($json);
    }
}
