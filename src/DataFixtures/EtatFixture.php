<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EtatFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $etat = new Etat();
        $etat->setLibelle('creation');
        $manager->persist($etat);
        $etat = new Etat();
        $etat->setLibelle('ouverte');
        $manager->persist($etat);
        $etat = new Etat();
        $etat->setLibelle('cloturee');
        $manager->persist($etat);
        $etat = new Etat();
        $etat->setLibelle('en_cours');
        $manager->persist($etat);
        $etat = new Etat();
        $etat->setLibelle('terminee');
        $manager->persist($etat);
        $etat = new Etat();
        $etat->setLibelle('annulee');
        $manager->persist($etat);
        $etat = new Etat();
        $etat->setLibelle('archivee');
        $manager->persist($etat);

        $manager->flush();
    }
}
