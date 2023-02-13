<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Repository\CampusRepository;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\ParticipantRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class SortieFixture extends Fixture implements DependentFixtureInterface
{
    public function __construct(private LieuRepository $lieuRepository,
                                private CampusRepository $campusRepository,
                                private EtatRepository $etatRepository,
                                private ParticipantRepository $participantRepository)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $listLieu = $this->lieuRepository->findAll();
        $listCampus = $this->campusRepository->findAll();
        $listEtat = $this->etatRepository->findAll();
        $listParticipant = $this->participantRepository->findAll();

        $creation = null;
        $ouverte = null;
        foreach($listEtat as $etat) {
            if($etat->getLibelle() == Etat::CREATION)
                $creation = $etat;
            elseif($etat->getLibelle() == Etat::OUVERTE)
                $ouverte = $etat;
        }

        for ($i = 0; $i < 15; $i++) {
            $sortie = new Sortie();
            $sortie->setNom($faker->sentence);
            $sortie->setDateHeureDebut($faker->dateTimeBetween('now','30 days'));
            $sortie->setDateLimiteInscription($faker->dateTimeBetween('-30 days'));
            $sortie->setDuree(rand(30,900));
            $sortie->setNbInscriptionsMax(rand(3,10));
            $sortie->setInfosSortie($faker->text(50));
            $sortie->setEtat(rand(0,1) == 1 ? $creation : $ouverte);
            $sortie->setCampus($listCampus[rand(0,count($listCampus)-1)]);
            $sortie->setLieu($listLieu[rand(0,count($listLieu)-1)]);
            $sortie->setOrganisateur($listParticipant[rand(0,count($listParticipant)-1)]);

            for($j = 0; $j < rand(2,$sortie->getNbInscriptionsMax()-1); $j++) {
                $sortie->addParticipant($listParticipant[rand(0,count($listParticipant)-1)]);
            }

            $manager->persist($sortie);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LieuFixture::class,
            CampusFixture::class,
            EtatFixture::class,
            ParticipantsFixture::class,
        ];
    }
}
