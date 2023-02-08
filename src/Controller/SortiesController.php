<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use mysql_xdevapi\Exception;
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
                                Security $security)
    {
        $json = array();

        if(!$request->isXmlHttpRequest()) {
            $json['error'] = "Bad request";
            return new JsonResponse($json);
        }

        $user = $security->getUser();
        var_dump($request->request->get('id'));
        $sortie = $sortieRepository->find(1);

        if (!$sortie)
            $json['error'] = "L'inscription à la sortie à échoué ! (sortie non trouvé)";
        elseif ($sortie->getParticipants()->count() >= $sortie->getNbInscriptionsMax())
            $json['error'] = "L'inscription à la sortie à échoué ! (nombre max de participants atteint)";
        else {
            if ($user instanceof Participant) {
                if ($sortie->getParticipants()->contains($user))
                    $json['error'] = "L'inscription à la sortie à échoué ! (vous participez déjà à la sortie)";
                $sortie->addParticipant($user);
                $entityManager->persist($sortie);
                $entityManager->flush();
            }
            $json['info'] = "OK";
        }

        return new JsonResponse($json);
    }
}
