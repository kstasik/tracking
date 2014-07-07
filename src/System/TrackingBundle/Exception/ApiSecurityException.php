<?php
namespace System\TrackingBundle\Exception;

class ApiSecurityException extends \RuntimeException
{
    public function getDetails(){
        return array('core' => 301, 'message' => $this->getMessage());
    }
}