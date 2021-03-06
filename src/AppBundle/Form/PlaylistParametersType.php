<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class PlaylistParametersType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('tracks_number', IntegerType::class, array('label' => 'Nombre de pistes', 'attr' => array('min' => 5, 'max' => 30)))
                ->add('save', SubmitType::class, array('label' => 'C\'est parti ! '))
        ;
    }

}