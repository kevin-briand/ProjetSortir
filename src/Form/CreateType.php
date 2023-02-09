<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la sortie'
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'html5' => true,
                'widget' => 'single_text',
                'label' => 'Date et heure de la sortie'
            ])
            ->add('dateLimiteInscription', DateType::class, [
                'html5' => true,
                'widget' => 'single_text',
                'label' => 'Date limite d\'inscription'
            ])
            ->add('nbInscriptionsMax', TextType::class, [
                'label' => 'Nombre de places'
            ])
            ->add('duree', NumberType::class, [
                'html5' => true
            ])
            ->add('infosSortie', TextType::class, [
                'label' => 'Description et infos'
            ])
            ->add('nomCampus', null, [
                'mapped' => false,
                'disabled' => true,
                'data' => $builder->getData()->getCampus()->getNom()
            ])
            ->add('ville', EntityType::class, [
                'label' => "Ville",
                'mapped' => false,
                'class' => Ville::class,
                'choice_label' => 'nom'
            ])
            ->add('lieu', EntityType::class, [
                'label' => "Lieu",
                'class' => Lieu::class,
                'choice_label' => 'nom'
            ])
            ->add('rue', null, [
                'mapped' => false,
                'label' => "Rue",
                'disabled' => true
            ])
            ->add('codePostal', null, [
                'mapped' => false,
                'label' => "Code postal",
                'disabled' => true,
            ])
            ->add('latitude', null, [
                'mapped' => false,
                'label' => 'Latitude'
            ])
            ->add('longitude', null, [
                'mapped' => false,
                'label' => 'Longitude'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
            "allow_extra_fields" => true
        ]);
    }
}