<?php

namespace App\Controller;

use App\Form\FilterType;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/sorties', name: 'sorties_')]
class SortiesController extends AbstractController
{
    #[Route('/', name: 'list')]
    public function list(SortieRepository $sortieRepository, Request $request, UserInterface $user): Response
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



        return $this->render('sorties/sorties.html.twig', [
            "sorties" => $sorties,
            "sortiesFilter" => $sortiesFilter
        ]);
    }


}
