<?php
namespace System\TrackingBundle\Handler;

use System\TrackingBundle\Form\DeviceType;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\FormFactoryInterface;
use System\TrackingBundle\Exception\InvalidFormException;
use System\TrackingBundle\Exception\ApiSecurityException;
use FOS\UserBundle\Doctrine\UserManager;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;

class DeviceHandler
{
    private $om;
    private $entityClass;
    private $repository;
    private $formFactory;
    private $um;
    private $se;
    
    public function __construct(ObjectManager $om, $entityClass, FormFactoryInterface $formFactory, UserManager $um, EncoderFactory $se)
    {
        $this->om = $om;
        $this->entityClass = $entityClass;
        $this->repository = $this->om->getRepository($this->entityClass);
        $this->formFactory = $formFactory;
        $this->um = $um;
        $this->se = $se;
    }
    
    /**
     * Get a Page.
     *
     * @param mixed $id
     */
    public function get($id)
    {
        return $this->repository->find($id);
    }
    
    public function post(array $parameters){
        try{
            $user = $this->um->loadUserByUsername($parameters['username']);
            $encoder = $this->se->getEncoder($user);
            
            if(!$encoder->isPasswordValid($user->getPassword(), $parameters['password'], $user->getSalt())){
                throw new ApiSecurityException('Invalid credentials password!');
            }
            
            $device = $this->createDevice();
            
            // set user
            $device->setUser($user);
            
            // add all objects from user by default
            foreach($user->getObjects() as $object){
                $device->addObject($object);
            }
            
            $form = $this->formFactory->create(new DeviceType(), $device, array('method' => 'POST'));
            unset($parameters['username'], $parameters['password']);
            $form->submit($parameters);
            
            if ($form->isValid()) {
                $device = $form->getData();
                $this->om->persist($device);
                $this->om->flush($device);
            
                return $device;
            }
        }
        catch(\Symfony\Component\Security\Core\Exception\UsernameNotFoundException $e){
            throw new ApiSecurityException('Invalid credentials!');
        }
        
        throw new InvalidFormException('Invalid submitted data', $form);
    }
    
    private function createDevice()
    {
        return new $this->entityClass();
    }
}