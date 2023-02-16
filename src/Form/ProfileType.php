<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Participant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo')
            ->add('nom')
            ->add('prenom')
            ->add('telephone', TelType::class)
            ->add('mail')
            ->add('campus', EntityType::class, [
                'disabled' => true,
                'class' => Campus::class,
                'choice_label' => 'nom' ])
            ->add('newPassword', PasswordType::class, [
                'mapped' => false,
                'label' => 'Nouveau mot de passe',
                'required' => false])
            ->add('confirmPassword', PasswordType::class, [
                'mapped' => false, // Empêche la liaison du champ avec l'entité
                'label' => 'Vérification nouveau mot de passe',
                'required' => false])
        ;

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
            "allow_extra_fields" => true // Autorise l'ajout de champs hors entité
        ]);
    }
}
