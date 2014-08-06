<?php

namespace Ginsberg\TransportationBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InstallationType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('isOpen', 'checkbox', array('label' => 'Site Open'))
            ->add('reservationsOpen', NULL, array('label' => 'Date to Open for Reservations'))
            ->add('carsAvailable', NULL, array('label' => 'Date Cars Become Available'))
            ->add('fallStart', 'datetime', array(
                'label' => 'Date Cars Picked Up from PTS for Fall Semester',
                'required' => FALSE,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd hh:mm a',
                'attr' => array(
                    'class' => 'datetime',
                  )
                ))
            ->add('thanksgivingStart', 'datetime', array(
                'label' => 'Thanksgiving Starts',
                'required' => FALSE,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd hh:mm a',
                'attr' => array(
                    'class' => 'datetime',
                  )
                ))
            ->add('thanksgivingEnd', 'datetime', array(
                'label' => 'Thanksgiving Ends',
                'required' => FALSE,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd hh:mm a',
                'attr' => array(
                    'class' => 'datetime',
                  )
                ))
            ->add('fallEnd', 'datetime', array(
                'label' => 'Date Cars Returned to PTS for Fall Semester',
                'required' => FALSE,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd hh:mm a',
                'attr' => array(
                    'class' => 'datetime',
                  )
                ))
            ->add('winterStart', 'datetime', array(
                'label' => 'Date Cars Picked Up from PTS for Winter Semester',
                'required' => FALSE,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd hh:mm a',
                'attr' => array(
                    'class' => 'datetime',
                  )
                ))
            ->add('mlkStart', 'datetime', array(
                'label' => 'MLK Starts',
                'required' => FALSE,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd hh:mm a',
                'attr' => array(
                    'class' => 'datetime',
                  )
                ))
            ->add('mlkEnd', 'datetime', array(
                'label' => 'MLK Ends',
                'required' => FALSE,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd hh:mm a',
                'attr' => array(
                    'class' => 'datetime',
                  )
                ))
            ->add('springbreakStart', 'datetime', array(
                'label' => 'Spring Break Starts',
                'required' => FALSE,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd hh:mm a',
                'attr' => array(
                    'class' => 'datetime',
                  )
                ))
            ->add('springbreakEnd', 'datetime', array(
                'label' => 'Spring Break Ends',
                'required' => FALSE,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd hh:mm a',
                'attr' => array(
                    'class' => 'datetime',
                  )
                ))
            ->add('winterEnd', 'datetime', array(
                'label' => 'Date Cars Returned to PTS for Winter Semester',
                'required' => FALSE,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd hh:mm a',
                'attr' => array(
                    'class' => 'datetime',
                  )
                ))
            ->add('fallBack', 'datetime', array(
                'label' => 'Fall Back (Daylight Saving Time Ends)',
                'required' => FALSE,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd hh:mm a',
                'attr' => array(
                    'class' => 'datetime',
                  )
                ))
            ->add('springForward', 'datetime', array(
                'label' => 'Spring Forward (Daylight Saving Time Begins)',
                'required' => FALSE,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd hh:mm a',
                'attr' => array(
                    'class' => 'datetime',
                  )
                ))
            ->add('dailyOpen', NULL, array('label' => 'Daily Opening (24-hr time)', 'empty_data' => '08:00'))
            ->add('dailyClose', NULL, array('label' => 'Daily Closing (24-hr time)', 'empty_data' => '20:00'))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ginsberg\TransportationBundle\Entity\Installation'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ginsberg_transportationbundle_installation';
    }
}
