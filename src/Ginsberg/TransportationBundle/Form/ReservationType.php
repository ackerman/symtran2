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
            ->add('dateToShow', 'datetime', array(
              'mapped' => FALSE,  
              'required' => FALSE,
              'widget' => 'single_text',
              'format' => 'yyyy-MM-dd',
              'empty_value' => date('Y-m-d'),
              'attr' => array(
                  'class' => 'datetime',
                )
              ))
            ->add('today', 'submit', array('label' => 'Today'))
            ->add('isRepeating', 'checkbox', array(
              'mapped' => FALSE,
              'label' => 'Repeats every week',
              'required' => FALSE,
            ))
            ->add('repeatsUntil', 'datetime', array(
              'mapped' => FALSE,
              'required' => FALSE,
              'label' => 'Repeats until',
              'constraints' => array(
                new \Ginsberg\TransportationBundle\Validator\Constraints\IsNotBlackedOut(),
              ),
              'widget' => 'single_text',
              'format' => 'yyyy-MM-dd 20:00',
              'attr' => array(
                'class' => 'datetime',
              )
            ))
            ->add('editSeries', 'checkbox', array(
              'mapped' => FALSE,
              'required' => FALSE,
            ))
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
            ->add('vehicle', NULL, array('required' => FALSE, 'empty_value' => 'Manually Select a Vehicle'))
            ->add('person')
            ->add('program', NULL, array('empty_value' => 'Select a Program'))
            ->add('seatsRequired')
            ->add('destination', NULL, array('required' => FALSE, 'empty_value' => 'Select a Destination'))
            ->add('destinationText', NULL, array('required' => FALSE))
            ->add('notes', NULL, array('required' => FALSE))
            ->add('isNoShow', NULL, array('required' => FALSE))
            ->add('created')
            ->add('modified', 'datetime', array('required' => FALSE))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ginsberg\TransportationBundle\Entity\Reservation',
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
