<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
                                Security $security): JsonResponse
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
        elseif ($sortie->getParticipants()->count() >= $sortie->getNbInscriptionsMax())
            $json['error'] = "L'inscription à la sortie ".$sortie->getNom()." à échoué ! (nombre max de participants atteint)";
        else {
            if ($user instanceof Participant) {
                if ($sortie->getParticipants()->contains($user)) {
                    $json['error'] = "L'inscription à la sortie ".$sortie->getNom()." à échoué ! (vous participez déjà à la sortie)";
                } else {
                    $sortie->addParticipant($user);
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
                                Security $security): JsonResponse
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
                    $json['error'] = "le désistement à la sortie à ".$sortie->getNom()." échoué ! (vous ne participez pas à la sortie)";
                } else {
                    $sortie->removeParticipant($user);
                    $entityManager->persist($sortie);
                    $entityManager->flush();
                    $json['info'] = "Désinscription à la sortie ".$sortie->getNom()." réussie !";
                }
            }
        }
        return new JsonResponse($json);
    }
}
