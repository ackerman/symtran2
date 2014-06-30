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
            ->add('status')
            ->add('dateApproved', 'datetime', array(
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd hh:mm a',
                'attr' => array(
                    'class' => 'datetime',
                  )
                ))
            ->add('isTermsAgreed')
            ->add('hasUnpaidTicket')
            ->add('created', 'datetime', array('widget' => 'text', 'empty_value' => 'Enter a date', 'read_only' => true))
            ->add('modified', 'datetime', array('widget' => 'text', 'empty_value' => 'Enter a date', 'read_only' => true))
            ->add('program')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ginsberg\TransportationBundle\Entity\Person'
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
