<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\CreateType;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use App\Security\SortieVoter;
use App\Workflow\EtatWorkflow;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

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
                               EtatWorkflow $etatWorkflow,
                               EtatRepository $etatRepository): Response
    {
        $user = $security->getUser();
        $sortie = $sortieRepository->find($id);
      
        $this->denyAccessUnlessGranted(SortieVoter::EDIT, $sortie);

        $prevInfo = 'Description initiale : '.$sortie->getInfosSortie();
        $sortieForm = $this->createForm(CreateType::class, $sortie);
        $sortieForm->handleRequest($request);
        if($etatWorkflow->canTransition($sortie,Etat::TRANS_ANNULATION)){
            if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
                $etatWorkflow->setEtat($sortie, Etat::TRANS_ANNULATION);
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
        }else{
            $this->addFlash('error', 'No bueno !');
            return $this->redirectToRoute('sorties_list');
        }
    }

    #[Route('/lieux', name: 'lieux')]
    public function lieux(Request $request,
                          VilleRepository $villeRepository,
                          LieuRepository $lieuRepository,
                          EntityManagerInterface $entityManager): JsonResponse
    {
        $json = $this->isJSONDatasValid($request);

        $ville = $villeRepository->find($request->request->get('id'));
        /*$lieu = $lieuRepository->findBy(['ville' => $ville ]); //seul moyen d'avoir les datas des lieux
        $spots = null;
        foreach ($lieu as $lie){
            $spots =+ [$lie->getId() => $lie->getNom()];
        }
       //$lieux = $ville->getLieux();
        $json = json_encode();
        //return new JsonResponse($json);/*/
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $json['info'] = serialize($ville->getLieux());
        return $this->json($ville,Response::HTTP_OK,[],['groups'=>'lieux']);
        //return $this->json($ville->getLieux()); //renvoie un truc tout vide

    }

    private function isJSONDatasValid(Request $request): array
    {
        $json = array();

        if (!$request->isXmlHttpRequest())
            $json['error'] = "Bad request";

        return $json;
    }
}