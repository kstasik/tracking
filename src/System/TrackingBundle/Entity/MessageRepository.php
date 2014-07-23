<?php
namespace System\TrackingBundle\Entity;

use Doctrine\ORM\EntityRepository;

class MessageRepository extends EntityRepository{
    public function getLastMessagesCollection(Device $device){
        return $this->getEntityManager()->createQuery('SELECT m FROM \System\TrackingBundle\Entity\Message m WHERE m.device = :device ORDER BY m.date_created DESC')
            ->setParameter('device', $device->getId())
            ->setMaxResults(10)
            ->getResult();
    }
}