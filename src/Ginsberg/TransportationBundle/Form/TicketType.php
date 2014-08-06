<?php

namespace Ginsberg\TransportationBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TicketType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('reservation', NULL, array(
                'label' => 'Reservation ID',
                'attr' => array(
                    'readonly' => 'readonly',
                )
            ))
            ->add('ticketDate', 'datetime', array(
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd hh:mm a',
                'label' => 'Ticket Date & Time',
                'attr' => array(
                    'class' => 'datetime',
                  )))
            ->add('reason', NULL, array(
                'label' => 'Reason/Code',
            ))
            ->add('location', NULL, array(
                'label' => 'Violation Location',
            ))
            ->add('amount')
            ->add('isPaid', NULL, array(
                'label' => 'Paid',
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ginsberg\TransportationBundle\Entity\Ticket'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ginsberg_transportationbundle_ticket';
    }
}
