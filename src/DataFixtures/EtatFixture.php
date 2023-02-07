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
        $etat->setLibelle('création');
        $manager->persist($etat);
        $etat = new Etat();
        $etat->setLibelle('ouverte');
        $manager->persist($etat);
        $etat = new Etat();
        $etat->setLibelle('cloturée');
        $manager->persist($etat);
        $etat = new Etat();
        $etat->setLibelle('terminée');
        $manager->persist($etat);
        $etat = new Etat();
        $etat->setLibelle('annulée');
        $manager->persist($etat);
        $etat = new Etat();
        $etat->setLibelle('archivée');
        $manager->persist($etat);

        $manager->flush();
    }
}
