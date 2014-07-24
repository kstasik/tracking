<?php
namespace System\TrackingBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections;

class MessageRepository extends EntityRepository{
    public function getLastMessagesCollection(Device $device){
        return $this->getEntityManager()->createQuery('SELECT m FROM \System\TrackingBundle\Entity\Message m WHERE m.device = :device ORDER BY m.date_created DESC')
            ->setParameter('device', $device->getId())
            ->setMaxResults(10)
            ->getResult();
    }
    
    public function getNearObjectMessages(Position $position){
        return new Collections\ArrayCollection( $this->getEntityManager()->createQuery('SELECT m FROM \System\TrackingBundle\Entity\Message m WHERE m.position = :position AND m.action = :type')
            ->setParameter('type', 'is_near_object')
            ->setParameter('position', $position->getId())
            ->getResult() );
    }
    
    public function getDevices(Collections\ArrayCollection $messages){
        $result = $messages->filter(function(Message $message){
            return $message->getResponse() == 1;
        });
        
        if($result->count() > 0){
            $list = new Collections\ArrayCollection();
            foreach($result as $message){
                $list->add($message->getDevice());
            }
            
            return $list;
        }
        
        return null;
    }
    
    public function areAllMessagesAnswered(Collections\ArrayCollection $messages){
        return $messages->filter(function(Message $message){
            return $message->getResponse() === null;
        })->count() == 0;
    }
}