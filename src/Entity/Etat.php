<?php

namespace App\Entity;

use App\Repository\EtatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;




#[ORM\Entity(repositoryClass: EtatRepository::class)]
#[UniqueEntity('libelle')]
class Etat
{
    const CREATION = 'creation';
    const CREATION_NAME = 'Création';
    const OUVERTE = 'ouverte';
    const OUVERTE_NAME = 'Ouverte';
    const CLOTUREE = 'cloturee';
    const CLOTUREE_NAME = 'Cloturée';
    const EN_COURS = 'en_cours';
    const EN_COURS_NAME = 'En cours';
    const TERMINEE = 'terminee';
    const TERMINEE_NAME = 'Terminée';
    const ANNULEE = 'annulee';
    const ANNULEE_NAME = 'Annulée';
    const ARCHIVEE = 'archivee';
    const ARCHIVEE_NAME = 'Archivée';
    const TRANS_CREATE = 'create';
    const TRANS_PUBLICATION = 'publication';
    const TRANS_REOUVERTURE = 'reouverture';
    const TRANS_CLOTURE = 'cloture';
    const TRANS_SORTIE_EN_COURS = 'sortie_en_cours';
    const TRANS_SORTIE_TERMINEE = 'sortie_terminee';
    const TRANS_ANNULATION = 'annulation';
    const TRANS_ARCHIVAGE = 'archivage';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $libelle = null;

    #[ORM\OneToMany(mappedBy: 'etat', targetEntity: Sortie::class)]
    private Collection $sorties;

    public function __construct()
    {
        $this->sorties = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getSorties(): Collection
    {
        return $this->sorties;
    }

    public function addSortie(Sortie $sortie): self
    {
        if (!$this->sorties->contains($sortie)) {
            $this->sorties->add($sortie);
            $sortie->setEtat($this);
        }

        return $this;
    }

    public function removeSortie(Sortie $sortie): self
    {
        if ($this->sorties->removeElement($sortie)) {
            // set the owning side to null (unless already changed)
            if ($sortie->getEtat() === $this) {
                $sortie->setEtat(null);
            }
        }

        return $this;
    }
}
