<?php
namespace System\TrackingBundle\Form\DataTransformer;

use Symfony\Component\Form\Extension\Core\DataTransformer\BaseDateTimeTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;

class DateTimeToSecondsTransformer extends BaseDateTimeTransformer
{
    public function transform($value)
    {        
        if (null === $value) {
            return;
        }
    
        if (!$value instanceof \DateTime) {
            throw new TransformationFailedException('Expected a \DateTime.');
        }
        
        $value = clone $value;
        try {
            $value->setTimezone(new \DateTimeZone($this->outputTimezone));
        } catch (\Exception $e) {
            throw new TransformationFailedException($e->getMessage(), $e->getCode(), $e);
        }
        
        return $value->format('H')*3600 + $value->format('i')*60 + $value->format('s');
    }
    
    public function reverseTransform($value)
    {
        if (null === $value) {
            return;
        }
        
        if (!is_numeric($value)) {
            throw new TransformationFailedException('Expected a numeric.');
        }
    
        try {
            $dateTime = new \DateTime('now');
            $dateTime->setTimezone(new \DateTimeZone($this->outputTimezone));
            $dateTime->setTime(($value/3600)%60, ($value/60)%60, $value%60);
        } catch (\Exception $e) {
            throw new TransformationFailedException($e->getMessage(), $e->getCode(), $e);
        }
    
        return $dateTime;
    }
}