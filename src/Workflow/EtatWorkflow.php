<?php

namespace App\Workflow;

use App\Entity\Etat;
use App\Entity\Sortie;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Workflow\WorkflowInterface;

/**
 * Classe de gestion des états de sortie
 * Utilisé pour la manipulation des états de l'entité Sortie
 * Lié au workflow etat_sortie
 */
class EtatWorkflow
{
    public function __construct(private readonly WorkflowInterface      $etatSortieStateMachine,
                                private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * Test et change automatiquement les états des sorties
     * @param Paginator $sorties
     * @return void
     */
    public function controleEtat(Paginator $sorties): void {
        foreach($sorties as $sortie) {
            /* @var Sortie $sortie */
            $dateActuelle = new \DateTime();
            $dateFinSortie = clone $sortie->getDateHeureDebut();
            $dateFinSortie = $dateFinSortie->add(\DateInterval::createFromDateString($sortie->getDuree() . ' minutes'));

            $dateArchivage = clone $dateFinSortie;
            $dateArchivage->add(\DateInterval::createFromDateString('1 month'));

            //Si le nombre de participants < participantMax
            if($this->etatSortieStateMachine->can($sortie,Etat::TRANS_REOUVERTURE) &&
                ($sortie->getDateLimiteInscription() > $dateActuelle &&
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
            if ($this->etatSortieStateMachine->can($sortie,Etat::TRANS_SORTIE_EN_COURS) &&
                $sortie->getDateHeureDebut() <= $dateActuelle)
            {
                $this->etatSortieStateMachine->apply($sortie,Etat::TRANS_SORTIE_EN_COURS);
            }
            //Si la sortie est terminée
            if ($this->etatSortieStateMachine->can($sortie,Etat::TRANS_SORTIE_TERMINEE) &&
                $dateFinSortie <= $dateActuelle)
            {
                $this->etatSortieStateMachine->apply($sortie,Etat::TRANS_SORTIE_TERMINEE);
            }
            //Si la sortie est terminée depuis plus d'un mois
            if ($this->etatSortieStateMachine->can($sortie,Etat::TRANS_ARCHIVAGE) &&
                $dateArchivage <= $dateActuelle)
            {
                $this->etatSortieStateMachine->apply($sortie,Etat::TRANS_ARCHIVAGE);
            }
            $this->entityManager->persist($sortie);
        }
        $this->entityManager->flush();
    }

    /**
     * Retourne l'état actuel de la sortie passée en paramètre
     * @param Sortie $sortie
     * @return string
     */
    public function getEtat(Sortie $sortie): string {
        return key($this->etatSortieStateMachine->getMarking($sortie)->getPlaces());
    }

    /**
     * Change l'état de la sortie passée en paramètre
     * @param Sortie $sortie
     * @param string $transition > passage d'une constrante TRANS_ de l'entité Etat
     * @return bool > retourne vrai si la transition à réussis
     */
    public function setEtat(Sortie $sortie, string $transition): bool {
        if($this->etatSortieStateMachine->can($sortie,$transition)) {
            $this->etatSortieStateMachine->apply($sortie, $transition);
            $this->entityManager->persist($sortie);
            $this->entityManager->flush();
            return true;
        }
        return false;
    }

    /**
     * Permet de tester si une transition est possible
     * @param Sortie $sortie
     * @param String $transition > passage d'une constrante TRANS_ de l'entité Etat
     * @return bool > retourne vrai si la transition est possible
     */
    public function canTransition(Sortie $sortie, String $transition) {
        return $this->etatSortieStateMachine->can($sortie, $transition);
    }

    /**
     * Retourne le nom de l'état de la sortie
     * @param Sortie $sortie
     * @return string
     */
    public function getEtatName(Sortie $sortie): string {
        return constant(strtoupper('App\Entity\Etat::'.$this->getEtat($sortie).'_NAME'));
    }
}