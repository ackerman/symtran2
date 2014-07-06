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
            ->add('isOpen')
            ->add('reservationsOpen')
            ->add('carsAvailable')
            ->add('fallStart')
            ->add('thanksgivingStart')
            ->add('thanksgivingEnd')
            ->add('fallEnd')
            ->add('winterStart')
            ->add('mlkStart')
            ->add('mlkEnd')
            ->add('springbreakStart')
            ->add('springbreakEnd')
            ->add('winterEnd')
            ->add('fallBack')
            ->add('springForward')
            ->add('dailyOpen')
            ->add('dailyClose')
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
