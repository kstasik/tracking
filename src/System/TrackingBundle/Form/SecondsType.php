<?php
namespace System\TrackingBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\ReversedTransformer;
use System\TrackingBundle\Form\DataTransformer\DateTimeToSecondsTransformer;

class SecondsType extends TimeType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        
        if ('seconds' === $options['input']) {
            $builder->addModelTransformer(new ReversedTransformer(
                new DateTimeToSecondsTransformer($options['model_timezone'], $options['model_timezone'])
            ));
        }
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);
        
        $resolver->setDefaults(array(
            'widget'         => 'single_text',
            'input'          => 'seconds'
        ));
        
        $resolver->setAllowedValues(array(
            'input' => array(
                'datetime',
                'string',
                'timestamp',
                'array',
                'seconds'
            )
        ));
    }
}