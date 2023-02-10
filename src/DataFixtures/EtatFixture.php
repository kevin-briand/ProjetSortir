<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EtatFixture extends Fixture
{
    public function load(ObjectManager $manager, ): void
    {

        $etat = new Etat();
        $etat->setLibelle(Etat::CREATION);
        $manager->persist($etat);
        $etat = new Etat();
        $etat->setLibelle(Etat::OUVERTE);
        $manager->persist($etat);
        $etat = new Etat();
        $etat->setLibelle(Etat::CLOTUREE);
        $manager->persist($etat);
        $etat = new Etat();
        $etat->setLibelle(Etat::EN_COURS);
        $manager->persist($etat);
        $etat = new Etat();
        $etat->setLibelle(Etat::TERMINEE);
        $manager->persist($etat);
        $etat = new Etat();
        $etat->setLibelle(Etat::ANNULEE);
        $etat = new Etat();
        $etat->setLibelle(Etat::ARCHIVEE);
        $manager->persist($etat);

        $manager->flush();
    }
}
