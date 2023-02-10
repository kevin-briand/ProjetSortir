<?php

namespace App\MarkingStore;

use App\Repository\EtatRepository;
use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\MarkingStore\MarkingStoreInterface;

class EtatSortie implements MarkingStoreInterface
{

    public function __construct(private EtatRepository $etatRepository)
    {
    }

    /**
     * @inheritDoc
     */
    public function getMarking(object $subject): Marking
    {
        $etat = $subject->getEtat();
        if (!$etat) {
            return new Marking();
        }

        return new Marking(array((string) $etat->getLibelle() => 0));
    }

    /**
     * @inheritDoc
     */
    public function setMarking(object $subject, Marking $marking, array $context = [])
    {
        $subject->setEtat($this->etatRepository->findOneBy(['libelle' => key($marking->getPlaces())]));
    }
}