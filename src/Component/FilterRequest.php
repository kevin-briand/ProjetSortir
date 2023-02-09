<?php

namespace App\Component;

use App\Entity\Campus;
use App\Entity\Participant;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class FilterRequest
{
    /**
     * @var Campus
     */
    public Campus $campus;

    /**
     * @var string|null
     */
    public ?string $nom;

    /**
     * @var DateTimeType|null
     */
    public ?\DateTimeInterface $dateDebut;

    /**
     * @var DateTimeType|null
     */
    public ?\DateTimeInterface $dateFin;

    /**
     * @var bool|null
     */
    public bool $organisateur;

    /**
     * @var bool|null
     */
    public bool $inscrit;

    /**
     * @var bool|null
     */
    public bool $nonInscrit;

    /**
     * @var bool|null
     */
    public bool $sortiesPassees;



}