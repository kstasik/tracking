<?php
namespace System\TrackingBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ObjectRepository extends EntityRepository{
    public function findByUser(User $user){
        return $this->findByUserQB($user)->getQuery()->getResult();
    }
    
    public function findByUserQB(User $user){
        $qb = $this->getEntityManager()->createQueryBuilder();
        
        $qb ->select(array('o'))
            ->from('SystemTrackingBundle:Object', 'o')
            ->join('o.users', 'u', 'WITH', 'u.id = :user')
            ->setParameter('user', $user->getId());

        return $qb;
    }
}