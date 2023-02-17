<?php

namespace App\Component;

use App\Entity\Campus;
use App\Entity\Participant;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class FilterRequest
{
    public ?Campus $campus = null;

    public ?string $nom = null;

    public ?\DateTime $dateDebut = null;

    public ?\DateTime $dateFin = null;

    public bool $organisateur = false;

    public bool $inscrit = false;

    public bool $nonInscrit = false;

    public bool $sortiesPassees = false;



}