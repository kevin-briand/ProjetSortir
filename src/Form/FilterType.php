<?php

namespace App\Form;

use App\Component\FilterRequest;
use App\Entity\Campus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('campus', EntityType::class, [
                'class'=>Campus::class,
                'choice_label' => 'nom',
                'attr' => ['class'=>'no-margin'],
            ])
            ->add('nom', TextType::class, [
                'required'=>false,
                'attr' => ['class'=>'no-margin']
            ])
            ->add('dateDebut', DateType::class,[
                'html5'=>true,
                'widget' => 'single_text',
                'label' => 'Entre',
                'required'=>false,
                'empty_data' => '',
                'by_reference' => false,
                'attr' => ['class'=>'no-margin']
            ])
            ->add('dateFin', DateType::class,[
                'html5'=>true,
                'widget' => 'single_text',
                'label' => 'et',
                'required'=>false,
                'empty_data' => '',
                'by_reference' => false,
                'attr' => ['class'=>'no-margin']
            ])
            ->add('organisateur', CheckboxType::class,[
                'required'=>false,
                'label' => 'Sorties dont je suis l\'organisat.eur.rice',
                'label_attr' => [
                    'class' => 'checkbox-inline col-form-label m-1',
                ],
            ])
            ->add('inscrit', CheckboxType::class,[
                'required'=>false,
                'label' => 'Sorties auxquelles je suis inscrit.e',
                'label_attr' => [
                    'class' => 'checkbox-inline col-form-label m-1',
                ],
            ])
            ->add('nonInscrit', CheckboxType::class,[
                'required'=>false,
                'label' => 'Sorties auxquelles je ne suis pas inscrit.e',
                'label_attr' => [
                    'class' => 'checkbox-inline col-form-label m-1',
                ],
            ])
            ->add('sortiesPassees', CheckboxType::class,[
                'required'=>false,
                'label' => 'Sorties passées',
                'label_attr' => [
                    'class' => 'checkbox-inline col-form-label m-1',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FilterRequest::class,
        ]);
    }
}
