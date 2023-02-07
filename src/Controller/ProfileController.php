<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/profile', name: 'profile_')]
class ProfileController extends AbstractController
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    #[Route(path: '', name: 'modifier')]
    public function modifier(Request $request, EntityManagerInterface $entityManager, Security $security): Response {

        $participant = $security->getUser();
        $participantForm = $this->createForm(ProfileType::class, $participant);

        $participantForm->handleRequest($request);

        if ($participant instanceof Participant && $participantForm->isSubmitted() && $participantForm->isValid()) { //instanceof pour caster l'objet user en Participant
            $mdp = $participantForm->get('newPassword')->getData(); // Extraction de l'input dans le formulaire
            $vmdp = $participantForm->get('confirmPassword')->getData();

            if($mdp != '' && $mdp === $vmdp) {
                $participant->setMotPasse($this->passwordHasher->hashPassword(
                    $participant,
                    $mdp
                )); // hashage du mot de passe
            }
            $entityManager->persist($participant);
            $entityManager->flush();

            $this->addFlash('success', 'Modification effectuée !');
            return $this->redirectToRoute('app_main');
        }

        return $this->render('security/profile.html.twig', [
            'participantForm' => $participantForm->createView()
        ]);
    }
}