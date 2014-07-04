<?php
namespace System\TrackingBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ObjectRepository extends EntityRepository{
    public function getObjectCollection(User $user){
        return $this->getEntityManager()->createQuery('SELECT o FROM \System\TrackingBundle\Entity\Object o LEFT JOIN o.users u WHERE u.id = :user')
            ->setParameter('user', $user->getId())
            ->getResult();
    }
}