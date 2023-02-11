<?php

namespace App\MarkingStore;

use App\Repository\EtatRepository;
use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\MarkingStore\MarkingStoreInterface;


/**
 * Classe de gestion des états de l'entité Sortie
 * Permet la conversion des états <> string
 * Inscrit en tant que service
 * Lié au workflow etat_sortie
 */
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

        return new Marking(array((string) $etat->getLibelle() => 1));

    }

    /**
     * @inheritDoc
     */
    public function setMarking(object $subject, Marking $marking, array $context = [])
    {
        $subject->setEtat($this->etatRepository->findOneBy(['libelle' => key($marking->getPlaces())]));
    }
}