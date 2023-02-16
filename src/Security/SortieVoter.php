<?php

namespace App\Security;

use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Workflow\EtatWorkflow;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SortieVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';
    const REMOVE = 'remove';

    public function __construct(private EtatWorkflow $etatWorkflow)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        if(!in_array($attribute, [self::VIEW, self::EDIT, self::REMOVE])) //Vérifie que l'attribut existe
            return false;

        if(!$subject instanceof Sortie) //Verifie que l'objet soit bien de type Sortie
            return false;

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if(in_array('ROLE_ADMIN', $user->getRoles()))
            return true;

        if (!$user instanceof Participant) //L'utilisateur doit être connecté
            return false;

        /** @var Sortie $sortie */
        $sortie = $subject;

        return match($attribute) {
            self::VIEW => $this->canView($sortie, $user),
            self::EDIT => $this->canEdit($sortie, $user),
            self::REMOVE => $this->canRemove($sortie, $user),
            default => throw new \LogicException('This code should not be reached!')
        };
    }

    private function canView(Sortie $sortie, Participant $user): bool
    {
        return true;
    }

    private function canEdit(Sortie $sortie, Participant $user): bool
    {
        if($sortie->getOrganisateur() !== $user || $this->etatWorkflow->getEtat($sortie) !==  Etat::CREATION)
            return false;

        return true;
    }

    private function canRemove(Sortie $sortie, Participant $user): bool
    {
        if($sortie->getOrganisateur() === $user &&
            $this->etatWorkflow->canTransition($sortie, Etat::TRANS_ANNULATION))
            return true;

        return false;
    }
}