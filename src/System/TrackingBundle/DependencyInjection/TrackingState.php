<?php
namespace System\TrackingBundle\DependencyInjection;
use System\TrackingBundle\Entity\User;
use Symfony\Component\Security\Core\SecurityContextInterface;

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
    
    public function getStatus(){
        if(!$this->status){
            $user = $this->getSecurityContext()->getToken()->getUser();
            $repo = $this->getEntityManager()->getRepository('SystemTrackingBundle:Position');
            
            $position = $repo->getLastPosition($user);
            if(!$position){
                $this->status = self::STATUS_NOT_INSTALLED;
            }
            else{
                $time = $position->getDateCreated()->getTimestamp();
                
                if( $time > time()-60*5 ){
                    $this->status = self::STATUS_ACTIVE;
                }
                elseif( $time > time()-60*15 ){
                    $this->status = self::STATUS_DELAYED;
                }
                else{
                    $this->status = self::STATUS_INACTIVE;
                }
            }
        }
        
        return $this->status;
    }
}