<?php
namespace System\TrackingBundle\DependencyInjection;
use System\TrackingBundle\Entity\User;
use Symfony\Component\Security\Core\SecurityContextInterface;
use System\TrackingBundle\Entity\Position;

class TrackingState{    
    const STATUS_NOT_INSTALLED  = 1;
    const STATUS_INACTIVE       = 2;
    const STATUS_DELAYED        = 3;
    const STATUS_ACTIVE         = 4;
    
    protected $em;
    
    protected $status;
    
    protected $securityContext;
    
    public function __construct(\Doctrine\ORM\EntityManager $em, SecurityContextInterface $securityContext){
        $this->em = $em;        
        $this->securityContext = $securityContext;
    }
    
    protected function getEntityManager(){
        return $this->em;
    }
    
    protected function getSecurityContext(){
        return $this->securityContext;
    }
    
    public function getLastPosition(){
        $user = $this->getSecurityContext()->getToken()->getUser();
        $repo = $this->getEntityManager()->getRepository('SystemTrackingBundle:Position');
        
        return $repo->getLastUserPosition($user);
    }

    public function getStatusUsingPosition(Position $position){
       $position = $this->getLastPosition();

       if($position){
            $time = $position->getDateCreated()->getTimestamp();
        
            if( $time > time()-60*5 ){
                return self::STATUS_ACTIVE;
            }
            elseif( $time > time()-60*15 ){
                return self::STATUS_DELAYED;
            }
            else{
                return self::STATUS_INACTIVE;
            }
        }

        return self::STATUS_NOT_INSTALLED;
    }
    
    public function getStatus(){
        if(!$this->status){
            $this->status = $this->getStatusUsingPosition($this->getLastPosition());
        }
        
        return $this->status;
    }
}