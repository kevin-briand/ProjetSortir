<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    public function list(SortieRepository $sortieRepository): Response
    {
        $sorties = $sortieRepository->findAllSorties();
        return $this->render('sorties/sorties.html.twig', [
            "sorties" => $sorties,
        ]);
    }

    #[Route('/inscription/', name: 'inscription')]
    public function inscription(Request $request,
                                SortieRepository $sortieRepository,
                                EntityManagerInterface $entityManager,
                                Security $security,
                                EtatRepository $etatRepository): JsonResponse
    {
        $json = array();

        if(!$request->isXmlHttpRequest()) {
            $json['error'] = "Bad request";
            return new JsonResponse($json);
        }

        $user = $security->getUser();
        $sortie = $sortieRepository->find($request->request->get('id'));

        if (!$sortie)
            $json['error'] = "L'inscription à la sortie ".$sortie->getNom()." à échoué ! (sortie non trouvé)";
        elseif ($sortie->getEtat() !== $etatRepository->findOneBy(['libelle'=> "en cours"]))
            $json['error'] = "L'inscription à la sortie ".$sortie->getNom()." à échoué ! (la sortie est ".$sortie->getEtat()->getLibelle().")";
        elseif ($sortie->getParticipants()->count() >= $sortie->getNbInscriptionsMax())
            $json['error'] = "L'inscription à la sortie ".$sortie->getNom()." à échoué ! (nombre max de participants atteint)";
        else {
            if ($user instanceof Participant) {
                if ($sortie->getParticipants()->contains($user)) {
                    $json['error'] = "L'inscription à la sortie ".$sortie->getNom()." à échoué ! (vous participez déjà à la sortie)";
                } else {
                    $sortie->addParticipant($user);
                    //Test si sortie pleine
                    if($sortie->getParticipants()->count() == $sortie->getNbInscriptionsMax()) {
                        $sortie->setEtat($etatRepository->findOneBy(['libelle'=> "cloturée"]));
                    }

                    $entityManager->persist($sortie);
                    $entityManager->flush();
                    $json['info'] = "Inscription à la sortie ".$sortie->getNom()." réussie !";
                }
            }
        }
        return new JsonResponse($json);
    }

    #[Route('/desistement/', name: 'desistement')]
    public function desistement(Request $request,
                                SortieRepository $sortieRepository,
                                EntityManagerInterface $entityManager,
                                Security $security,
                                EtatRepository $etatRepository): JsonResponse
    {
        $json = array();

        if(!$request->isXmlHttpRequest()) {
            $json['error'] = "Bad request";
            return new JsonResponse($json);
        }

        $user = $security->getUser();
        $sortie = $sortieRepository->find($request->request->get('id'));

        if (!$sortie)
            $json['error'] = "Le désistement à la sortie à ".$sortie->getNom()." échoué ! (sortie non trouvé)";
        else {
            if ($user instanceof Participant) {
                if (!$sortie->getParticipants()->contains($user)) {
                    $json['error'] = "le désistement à la sortie à " . $sortie->getNom() . " échoué ! (vous ne participez pas à la sortie)";
                } elseif ($sortie->getEtat() !== $etatRepository->findOneBy(['libelle'=> "en cours"]) &&
                    $sortie->getEtat() !== $etatRepository->findOneBy(['libelle'=> "cloturée"])) {
                    $json['error'] = "le désistement à la sortie à " . $sortie->getNom() . " échoué ! (la sortie ne peux pas être modifiée)";
                } else {
                    $sortie->removeParticipant($user);
                    //Changement d'état si la date le permet
                    if($sortie->getDateLimiteInscription() > new Date()) {
                        $sortie->setEtat($etatRepository->findOneBy(['libelle'=> "en cours"]));
                    }
                    $entityManager->persist($sortie);
                    $entityManager->flush();
                    $json['info'] = "Désinscription à la sortie ".$sortie->getNom()." réussie !";
                }
            }
        }
        return new JsonResponse($json);
    }
}
