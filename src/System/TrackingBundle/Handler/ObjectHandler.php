<?php
namespace System\TrackingBundle\Handler;

use System\TrackingBundle\Form\DeviceType;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\FormFactoryInterface;
use System\TrackingBundle\Exception\InvalidFormException;
use System\TrackingBundle\Exception\ApiSecurityException;
use FOS\UserBundle\Doctrine\UserManager;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;

class ObjectHandler
{
    private $om;
    private $entityClass;
    private $repository;
    
    public function __construct(ObjectManager $om, $entityClass)
    {
        $this->om = $om;
        $this->entityClass = $entityClass;
        $this->repository = $this->om->getRepository($this->entityClass);
    }
    
    public function get($id)
    {
        return $this->repository->find($id);
    }
    
    private function createDevice()
    {
        return new $this->entityClass();
    }
}