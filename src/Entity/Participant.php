<?php

namespace App\Entity;

use App\Repository\ParticipantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParticipantRepository::class)]
class Participant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    #[ORM\Column(length: 50)]
    private ?string $prenom = null;

    #[ORM\Column]
    private ?int $telephone = null;

    #[ORM\Column(length: 50)]
    private ?string $mail = null;

    #[ORM\Column(length: 255)]
    private ?string $motPasse = null;

    #[ORM\Column]
    private ?bool $administrateur = null;

    #[ORM\Column]
    private ?bool $actif = null;

    #[ORM\ManyToOne(inversedBy: 'participant')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Campus $campus = null;

    #[ORM\ManyToMany(targetEntity: Sortie::class, inversedBy: 'participants')]
    private Collection $inscriptionSortie;

    #[ORM\OneToMany(mappedBy: 'organisateur', targetEntity: Sortie::class)]
    private Collection $organisationSortie;

    public function __construct()
    {
        $this->inscriptionSortie = new ArrayCollection();
        $this->organisationSortie = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTelephone(): ?int
    {
        return $this->telephone;
    }

    public function setTelephone(int $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    public function getMotPasse(): ?string
    {
        return $this->motPasse;
    }

    public function setMotPasse(string $motPasse): self
    {
        $this->motPasse = $motPasse;

        return $this;
    }

    public function isAdministrateur(): ?bool
    {
        return $this->administrateur;
    }

    public function setAdministrateur(bool $administrateur): self
    {
        $this->administrateur = $administrateur;

        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): self
    {
        $this->actif = $actif;

        return $this;
    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): self
    {
        $this->campus = $campus;

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getInscriptionSortie(): Collection
    {
        return $this->inscriptionSortie;
    }

    public function addInscriptionSortie(Sortie $inscriptionSortie): self
    {
        if (!$this->inscriptionSortie->contains($inscriptionSortie)) {
            $this->inscriptionSortie->add($inscriptionSortie);
        }

        return $this;
    }

    public function removeInscriptionSortie(Sortie $inscriptionSortie): self
    {
        $this->inscriptionSortie->removeElement($inscriptionSortie);

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getOrganisationSortie(): Collection
    {
        return $this->organisationSortie;
    }

    public function addOrganisationSortie(Sortie $organisationSortie): self
    {
        if (!$this->organisationSortie->contains($organisationSortie)) {
            $this->organisationSortie->add($organisationSortie);
            $organisationSortie->setOrganisateur($this);
        }

        return $this;
    }

    public function removeOrganisationSortie(Sortie $organisationSortie): self
    {
        if ($this->organisationSortie->removeElement($organisationSortie)) {
            // set the owning side to null (unless already changed)
            if ($organisationSortie->getOrganisateur() === $this) {
                $organisationSortie->setOrganisateur(null);
            }
        }

        return $this;
    }
}
