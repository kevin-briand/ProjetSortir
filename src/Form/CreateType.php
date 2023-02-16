<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la sortie :'
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'html5' => true,
                'widget' => 'single_text',
                'label' => 'Date et heure de la sortie :'
            ])
            ->add('dateLimiteInscription', DateType::class, [
                'html5' => true,
                'widget' => 'single_text',
                'label' => 'Date limite d\'inscription :'
            ])
            ->add('nbInscriptionsMax', TextType::class, [
                'label' => 'Nombre de places :'
            ])
            ->add('duree', NumberType::class, [
                'html5' => true
            ])
            ->add('infosSortie', TextType::class, [
                'label' => 'Description et infos :'
            ])
            ->add('nomCampus', null, [
                'mapped' => false,
                'disabled' => true,
                'label' => 'Campus :',
                'data' => $builder->getData()->getCampus()->getNom()
            ])
            ->add('ville', EntityType::class, [
                'label' => "Ville :",
                'mapped' => false,
                'class' => Ville::class,
                'choice_label' => 'nom'
            ])
            ->add('lieu', ChoiceType::class, [
                'label' => "Lieu :",
                'mapped' => false,
                //en gros where lieu getville = ville id
                'disabled' => true
                //data ?
            ])
            ->add('rue', null, [
                'mapped' => false,
                'label' => "Rue :",
                'disabled' => true
            ])
            ->add('codePostal', null, [
                'mapped' => false,
                'label' => "Code postal :",
                'disabled' => true,
            ])
            ->add('latitude', null, [
                'mapped' => false,
                'label' => 'Latitude :'
            ])
            ->add('longitude', null, [
                'mapped' => false,
                'label' => 'Longitude :'
            ])
        ;

        //nous permet d'ajouter en back la liste des lieux selon la ville choisie
        $formModifier = function (FormInterface $form, int $choices = null){
            //avec le post qu'on a fait à côté,
            //c'est ici que l'on veut qu'il aille nous récup les lieux dont la ville matche le choix fait dans le form
            //$lieux = null === $lieu ? [] : $ville->getLieux();
            $form->add('lieu', ChoiceType::class, [
                'label' => 'Lieu :',
                'choices' => $choices,
                //en gros where lieu getville = ville id
                'disabled' => true
            ]);
        };
        //permet de préparer le terrain au chargement de la page : au début ça init choix vide via methode formModifier
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier){
                $data = $event->getData();
                $formModifier($event->getForm());// $data->getLieu());
            }
        );
        //après choix du lieu => rebond vers la méthode formModifier avec params envoyés pour update les choix en back
       $builder->get('lieu')->addEventListener(
            FormEvents::PRE_SUBMIT,
            function(FormEvent $event) use ($formModifier) { //use ($formModifier){
                $choices = $event->getForm()->getData();
                //en gros ce serait ici qu'on viendrait fetch nos lieux depuis le json ?
                $formModifier($event->getForm()->getParent(), $choices);
            }
        );

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
            "allow_extra_fields" => true
        ]);
    }

}
