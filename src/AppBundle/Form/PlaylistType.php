<?php

namespace AppBundle\Form;

use AppBundle\Entity\Track;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlaylistType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('name', null, array(
            'required' => true,
            'empty_data' => date('d/m/Y H:i:s')
        ))
        ->add('tracks', CollectionType::class, array(
            'entry_type' => TrackType::class,
            'entry_options' => array('label' => false)
        ))
        ->add('save', SubmitType::class, array('label' => 'Envoyer playlist'));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Track::class,
        ));
    }

}