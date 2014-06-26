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
            ->add('first_name')
            ->add('last_name')
            ->add('uniqname')
            ->add('phone')
            ->add('status')
            ->add('date_approved')
            ->add('is_terms_agreed')
            ->add('is_ticket_unpaid')
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
