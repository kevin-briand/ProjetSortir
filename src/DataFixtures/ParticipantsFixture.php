<?php

namespace App\DataFixtures;

use App\Entity\Participant;
use App\Repository\CampusRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ParticipantsFixture extends Fixture implements DependentFixtureInterface
{
    public function __construct(private CampusRepository $campusRepository, private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $listCampus = $this->campusRepository->findAll();

        for ($i = 0; $i < 25; $i++) {
            $participant = new Participant();
            $participant->setNom($faker->lastName);
            $participant->setPrenom($faker->firstName);
            $participant->setMail($faker->unique()->email);
            $participant->setPseudo($faker->unique()->userName);
            $participant->setTelephone('06'.strval(rand(10000000,99999999)));
            $participant->setActif(true);
            $participant->setAdministrateur(false);
            $participant->setMotPasse($this->passwordHasher->hashPassword(
                $participant,
                'test'
            ));
            $participant->setCampus($listCampus[rand(0, count($listCampus)-1)]);

            $manager->persist($participant);
        }

        $manager->flush();
    }


    public function getDependencies(): array
    {
        return [
            CampusFixture::class,
        ];
    }
}
