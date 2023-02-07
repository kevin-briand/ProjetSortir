<?php

namespace App\DataFixtures;

use App\Entity\Lieu;
use App\Repository\VilleRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class LieuFixture extends Fixture implements DependentFixtureInterface
{
    public function __construct(private VilleRepository $villeRepository)
    {
    }
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $listVille = $this->villeRepository->findAll();

        for ($i = 0; $i < 15; $i++) {
            $lieu = new Lieu();
            $lieu->setNom($faker->name);
            $lieu->setRue($faker->streetAddress);
            $lieu->setLatitude($faker->latitude);
            $lieu->setLongitude($faker->longitude);
            $lieu->setVille($listVille[rand(0, count($listVille)-1)]);

            $manager->persist($lieu);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            VilleFixture::class,
        ];
    }
}
