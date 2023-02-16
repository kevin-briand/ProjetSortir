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
//use JMS\Serializer\Serializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
                           LieuRepository $lieuRepository,
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
            //seul souci qui coince : récupérer l'ID envoyé par le formulaire pour pouvoir le glisser dans l'obj juste avant l'envoi
            $sortie->setLieu($lieuRepository->findOneBy(['id' => $request->request->all()['create']['lieu']]));
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
                                 LieuRepository $lieuRepository,
                                 EtatWorkflow $etatWorkflow,
                                 SortieRepository $sortieRepository): Response
    {
        $user = $security->getUser();
        $sortie = $sortieRepository->find($id);
        $rustine = $sortie->getLieu()->getNom();
        $sortie->setLieu(null);
        $this->denyAccessUnlessGranted(SortieVoter::EDIT, $sortie);

        $sortieForm = $this->createForm(CreateType::class, $sortie);
        $sortieForm->add('lieu', ChoiceType::class, [
            'choices' => [$rustine => $rustine],
            'disabled' => true,
        ] );

        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $sortie->setLieu($lieuRepository->findOneBy(['id' => $request->request->all()['create']['lieu']]));
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
                               LieuRepository $lieuRepository,
                               EtatWorkflow $etatWorkflow): Response
    {
        $user = $security->getUser();
        $sortie = $sortieRepository->find($id);
        $prevInfo = 'Description initiale : '.$sortie->getInfosSortie();
        $prevLieu = $sortie->getLieu();
        $rustine = $sortie->getLieu()->getNom();
        $sortie->setLieu(null);

        $this->denyAccessUnlessGranted(SortieVoter::REMOVE, $sortie);


        $sortieForm = $this->createForm(CreateType::class, $sortie);
        $sortieForm->add('lieu', ChoiceType::class, [
            'choices' => [$rustine => $rustine],
            'disabled' => true,
        ] );
        $sortie->setLieu($prevLieu);
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

    #[Route('/lieux', name: 'lieux')]
    public function lieux(Request $request,
                          VilleRepository $villeRepository,
                          LieuRepository $lieuRepository,
                          EntityManagerInterface $entityManager): JsonResponse
    {
        $json = $this->isJSONDatasValid($request);

        $ville = $villeRepository->find($request->request->get('id'));
        $lieu = $lieuRepository->findBy(['ville' => $ville ]); //seul moyen d'avoir les datas des lieux

       //$lieux = $ville->getLieux();
      //  $json = json_encode();
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