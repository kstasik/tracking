<?php
namespace System\TrackingBundle\Security\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Security\UserProvider;
use FOS\UserBundle\Model\UserManagerInterface;

class DeviceApiKeyUserProvider extends UserProvider
{
    private $om;
    
    public function __construct(UserManagerInterface $userManager, ObjectManager $om)
    {
        parent::__construct($userManager);
        
        $this->om = $om;
    }
    
    public function getUsernameForApiKey($api_key)
    {
        $device = $this->om->getRepository('System\TrackingBundle\Entity\Device')->findOneBy(array('api_key' => $api_key));
        
        if(!$device){
            return false;
        }

        return $device->getUser()->getUsername();
    }

}