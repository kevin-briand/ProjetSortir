<?php

namespace App\Workflow;

use App\Entity\Sortie;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Workflow\WorkflowInterface;

class EtatWorkflow
{
    public function __construct(private readonly WorkflowInterface      $etatSortieStateMachine,
                                private readonly EntityManagerInterface $entityManager)
    {
    }

    public function controleEtat(Paginator $sorties): void {
        foreach($sorties as $sortie) {
            /* @var Sortie $sortie */
            $dateActuelle = new Date;
            $dateFinSortie = $sortie->getDateHeureDebut();
            $dateFinSortie->add(\DateInterval::createFromDateString($sortie->getDuree() . ' minutes'));
            $dateArchivage = $dateFinSortie;
            $dateArchivage->add(\DateInterval::createFromDateString('1 month'));

            //Si le nombre de participants < participantMax
            if($this->etatSortieStateMachine->can($sortie,'reouverture') &&
                ($sortie->getDateLimiteInscription() > $dateActuelle ||
                    $sortie->getParticipants()->count() < $sortie->getNbInscriptionsMax()))
            {
                $this->etatSortieStateMachine->apply($sortie,'reouverture');
            }
            //Si le nombre de participants > participantMax
            elseif($this->etatSortieStateMachine->can($sortie,'cloture') &&
                ($sortie->getDateLimiteInscription() <= $dateActuelle ||
                    $sortie->getParticipants()->count() >= $sortie->getNbInscriptionsMax()))
            {
                $this->etatSortieStateMachine->apply($sortie,'cloture');
            }
            //Si la sortie a commencée
            elseif ($this->etatSortieStateMachine->can($sortie,'sortie_en_cours') &&
                $sortie->getDateHeureDebut() <= $dateActuelle)
            {
                $this->etatSortieStateMachine->apply($sortie,'sortie_en_cours');
            }
            //Si la sortie est terminée
            elseif ($this->etatSortieStateMachine->can($sortie,'sortie_terminee') &&
                $dateFinSortie <= $dateActuelle)
            {
                $this->etatSortieStateMachine->apply($sortie,'sortie_terminee');
            }
            //Si la sortie est terminée depuis plus d'un mois
            elseif ($this->etatSortieStateMachine->can($sortie,'archivage') &&
                $dateArchivage <= $dateActuelle)
            {
                $this->etatSortieStateMachine->apply($sortie,'archivage');
            }
            $this->entityManager->persist($sortie);
        }
        $this->entityManager->flush();
    }

    public function publier(Sortie $sortie): bool {
        if($this->etatSortieStateMachine->can($sortie,'publication')) {
            $this->etatSortieStateMachine->apply($sortie,'publication');
            $this->entityManager->persist($sortie);
            $this->entityManager->flush();
            return true;
        }
        return false;
    }

    public function annuler(Sortie $sortie): bool {
        if($this->etatSortieStateMachine->can($sortie,'annulation')) {
            if($this->etatSortieStateMachine->getMarking($sortie) == 'create') {
                $this->entityManager->remove($sortie);
            } else {
                $this->etatSortieStateMachine->apply($sortie, 'annulation');
                $this->entityManager->persist($sortie);
                $this->entityManager->flush();
            }
            return true;
        }
        return false;
    }
}