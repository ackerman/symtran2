<?php

namespace Ginsberg\TransportationBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PersonType extends AbstractType
{
     /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName')
            ->add('lastName')
            ->add('uniqname')
            ->add('phone')
            ->add('status', NULL, array('required' => FALSE))
            ->add('dateApproved', 'datetime', array(
                'required' => FALSE,
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd hh:mm a',
                'attr' => array(
                    'class' => 'datetime',
                  )
                ))
            ->add('isTermsAgreed', 'checkbox', array('required' => FALSE))
            ->add('hasUnpaidTicket', 'checkbox', array('required' => FALSE))
            ->add('created', 'datetime', array('widget' => 'text', 'empty_value' => 'Enter a date', 'read_only' => true, 'required' => FALSE))
            ->add('modified', 'datetime', array('widget' => 'text', 'empty_value' => 'Enter a date', 'read_only' => true, 'required' => FALSE))
            ->add('program', null, array('empty_value' => 'Select a Program', 'required' => FALSE))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ginsberg\TransportationBundle\Entity\Person',
            'validation_groups' => array('registration', 'Person', 'Default')
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ginsberg_transportationbundle_person';
    }
}
