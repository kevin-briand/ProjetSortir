<?php

namespace App\Entity;

use App\Repository\ParticipantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ParticipantRepository::class)]
#[UniqueEntity('mail')]
#[UniqueEntity('pseudo')]
class Participant implements UserInterface, PasswordAuthenticatedUserInterface

{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    #[ORM\Column(length: 50)]
    private ?string $prenom = null;

    #[ORM\Column(length: 10)]
    #[Assert\Regex('/^\d{10}$/')]
    private ?string $telephone = null;

    #[ORM\Column(length: 50, unique: true)]
    private ?string $mail = null;

    #[ORM\Column(length: 255)]
    private ?string $motPasse = null;

    #[ORM\Column]
    private ?bool $administrateur = null;

    #[ORM\Column]
    private ?bool $actif = null;

    #[ORM\ManyToOne(inversedBy: 'participants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Campus $campus = null;

    #[ORM\ManyToMany(targetEntity: Sortie::class, inversedBy: 'participants')]
    private Collection $inscriptionsSorties;

    #[ORM\OneToMany(mappedBy: 'organisateur', targetEntity: Sortie::class)]
    private Collection $organisationSorties;

    #[ORM\Column(length: 30, unique: true)]
    private ?string $pseudo = null;

    public function __construct()
    {
        $this->inscriptionsSorties = new ArrayCollection();
        $this->organisationSorties = new ArrayCollection();
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

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): self
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
    public function getInscriptionsSorties(): Collection
    {
        return $this->inscriptionsSorties;
    }

    public function addInscriptionSortie(Sortie $inscriptionSortie): self
    {
        if (!$this->inscriptionsSorties->contains($inscriptionSortie)) {
            $this->inscriptionsSorties->add($inscriptionSortie);
        }

        return $this;
    }

    public function removeInscriptionSortie(Sortie $inscriptionSortie): self
    {
        $this->inscriptionsSorties->removeElement($inscriptionSortie);

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getOrganisationSorties(): Collection
    {
        return $this->organisationSorties;
    }

    public function addOrganisationSortie(Sortie $organisationSortie): self
    {
        if (!$this->organisationSorties->contains($organisationSortie)) {
            $this->organisationSorties->add($organisationSortie);
            $organisationSortie->setOrganisateur($this);
        }

        return $this;
    }

    public function removeOrganisationSortie(Sortie $organisationSortie): self
    {
        if ($this->organisationSorties->removeElement($organisationSortie)) {
            // set the owning side to null (unless already changed)
            if ($organisationSortie->getOrganisateur() === $this) {
                $organisationSortie->setOrganisateur(null);
            }
        }

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getRoles(): array
    {
        return $this->administrateur ? ['ROLE_ADMIN'] : ['ROLE_USER'];
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getUserIdentifier(): string
    {
        return $this->pseudo;
    }

    public function getPassword(): ?string
    {
        return $this->motPasse;
    }

    public function setPassword(string $password): self
    {
        $this->motPasse = $password;

        return $this;
    }
}
