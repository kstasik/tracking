<?php
namespace System\TrackingBundle\Entity;

use Doctrine\ORM\EntityRepository;

class DeviceRepository extends EntityRepository{
    public function getDeviceCollection(User $user){
        return $this->getEntityManager()->createQuery('SELECT d FROM \System\TrackingBundle\Entity\Device d WHERE d.user = :user')
            ->setParameter('user', $user->getId())
            ->getResult();
    }
    
    public function findWithNoDataAlerts(){
        return $this->getEntityManager()->createQuery('SELECT d FROM \System\TrackingBundle\Entity\Device d WHERE (d.nodata_timeout IS NOT NULL OR d.nodata_critical_timeout IS NOT NULL) AND d.alerts_enabled = 1')
            ->getResult();
    }
    
    public function findWithAlertsEnabled(){
        return $this->getEntityManager()->createQuery('SELECT d FROM \System\TrackingBundle\Entity\Device d WHERE d.alerts_enabled = 1')
        ->getResult();
    }
}