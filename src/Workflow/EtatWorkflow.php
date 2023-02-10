<?php

namespace App\Workflow;

use App\Entity\Etat;
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
            if($this->etatSortieStateMachine->can($sortie,Etat::TRANS_REOUVERTURE) &&
                ($sortie->getDateLimiteInscription() > $dateActuelle ||
                    $sortie->getParticipants()->count() < $sortie->getNbInscriptionsMax()))
            {
                $this->etatSortieStateMachine->apply($sortie,Etat::TRANS_REOUVERTURE);
            }
            //Si le nombre de participants > participantMax
            elseif($this->etatSortieStateMachine->can($sortie,Etat::TRANS_CLOTURE) &&
                ($sortie->getDateLimiteInscription() <= $dateActuelle ||
                    $sortie->getParticipants()->count() >= $sortie->getNbInscriptionsMax()))
            {
                $this->etatSortieStateMachine->apply($sortie,Etat::TRANS_CLOTURE);
            }
            //Si la sortie a commencée
            elseif ($this->etatSortieStateMachine->can($sortie,Etat::TRANS_SORTIE_EN_COURS) &&
                $sortie->getDateHeureDebut() <= $dateActuelle)
            {
                $this->etatSortieStateMachine->apply($sortie,Etat::TRANS_SORTIE_EN_COURS);
            }
            //Si la sortie est terminée
            elseif ($this->etatSortieStateMachine->can($sortie,Etat::TRANS_SORTIE_TERMINEE) &&
                $dateFinSortie <= $dateActuelle)
            {
                $this->etatSortieStateMachine->apply($sortie,Etat::TRANS_SORTIE_TERMINEE);
            }
            //Si la sortie est terminée depuis plus d'un mois
            elseif ($this->etatSortieStateMachine->can($sortie,Etat::TRANS_ARCHIVAGE) &&
                $dateArchivage <= $dateActuelle)
            {
                $this->etatSortieStateMachine->apply($sortie,Etat::TRANS_ARCHIVAGE);
            }
            $this->entityManager->persist($sortie);
        }
        $this->entityManager->flush();
    }

    public function publier(Sortie $sortie): bool {
        if($this->etatSortieStateMachine->can($sortie,Etat::TRANS_PUBLICATION)) {
            $this->etatSortieStateMachine->apply($sortie,Etat::TRANS_PUBLICATION);
            $this->entityManager->persist($sortie);
            $this->entityManager->flush();
            return true;
        }
        return false;
    }

    public function annuler(Sortie $sortie): bool {
        if($this->etatSortieStateMachine->can($sortie,Etat::TRANS_ANNULATION)) {
            if($this->etatSortieStateMachine->getMarking($sortie) == Etat::TRANS_CREATE) {
                $this->entityManager->remove($sortie);
            } else {
                $this->etatSortieStateMachine->apply($sortie, Etat::TRANS_ANNULATION);
                $this->entityManager->persist($sortie);
                $this->entityManager->flush();
            }
            return true;
        }
        return false;
    }

    public function getNomEtats(): array {
        var_dump($this->etatSortieStateMachine->getMetadataStore()->getWorkflowMetadata());
        return array();
    }
}