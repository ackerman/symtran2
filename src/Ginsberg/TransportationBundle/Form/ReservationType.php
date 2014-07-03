<?php

namespace Ginsberg\TransportationBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ReservationType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('start', 'datetime', array(
                'required' => TRUE,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd hh:mm a',
                'attr' => array(
                    'class' => 'datetime',
                  )
                ))
            ->add('end', 'datetime', array(
                'required' => TRUE,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd hh:mm a',
                'attr' => array(
                    'class' => 'datetime',
                  )
                ))
            ->add('checkout', 'datetime', array(
                'required' => FALSE,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd hh:mm a',
                'attr' => array(
                    'class' => 'datetime',
                  )
                ))
            ->add('checkin', 'datetime', array(
                'required' => FALSE,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd hh:mm a',
                'attr' => array(
                    'class' => 'datetime',
                  )
                ))
            ->add('vehicle', NULL, array('empty_value' => 'Manually Select a Vehicle'))
            ->add('person')
            ->add('program')
            ->add('seatsRequired')
            ->add('destinationText')
            ->add('notes')
            ->add('isNoShow')
            ->add('created')
            ->add('modified')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ginsberg\TransportationBundle\Entity\Reservation'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ginsberg_transportationbundle_reservation';
    }
}
