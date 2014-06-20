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
            ->add('start')
            ->add('end')
            ->add('out')
            ->add('in')
            ->add('created')
            ->add('modified')
            ->add('seats_required')
            ->add('destination_text')
            ->add('notes')
            ->add('is_noshow')
            ->add('person')
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
